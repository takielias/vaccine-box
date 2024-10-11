<?php

namespace Tests\Unit;

use App\Contracts\Repositories\VaccineRegistrationRepositoryInterface;
use App\Enums\VaccinationStatus;
use App\Models\User;
use App\Models\Vaccination;
use App\Models\VaccinationCenter;
use App\Services\VaccineRegistrationService;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class VaccineRegistrationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $mockRepository;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(VaccineRegistrationRepositoryInterface::class);
        $this->service = new VaccineRegistrationService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetNextAvailableVaccinationDate()
    {
        // Arrange
        $centerId = Str::ulid()->toString();
        $startDate = Carbon::create(2024, 1, 1, 8, 0, 0); // A Monday at 8:00 AM
        $endDate = $startDate->copy()->addDays(90);

        $vaccinationCenter = new VaccinationCenter([
            'id' => $centerId,
            'name' => 'Test Center',
            'daily_capacity' => 100,
        ]);

        $this->mockRepository->shouldReceive('getVaccinationCenter')
            ->with($centerId)
            ->andReturn($vaccinationCenter);

        $this->mockRepository->shouldReceive('getVaccinationCountsByDateRange')
            ->with($centerId, Mockery::type(Carbon::class), Mockery::type(Carbon::class))
            ->andReturn(collect([
                '2024-01-01' => (object) ['scheduled_count' => 100], // Monday - Full
                '2024-01-02' => (object) ['scheduled_count' => 50],  // Tuesday - Available
                '2024-01-03' => (object) ['scheduled_count' => 100], // Wednesday - Full
                '2024-01-04' => (object) ['scheduled_count' => 100], // Thursday - Full
                '2024-01-05' => (object) ['scheduled_count' => 0],   // Friday - Skipped
                '2024-01-06' => (object) ['scheduled_count' => 0],   // Saturday - Skipped
                '2024-01-07' => (object) ['scheduled_count' => 0],   // Sunday - Available
            ]));

        // Act
        $result = $this->service->getNextAvailableVaccinationDate($centerId, $startDate);

        // Assert
        $this->assertNotNull($result);
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2024-01-02', $result->format('Y-m-d'));
    }

    public function testGetNextAvailableVaccinationDateWhenStartDateIsWeekend()
    {
        // Arrange
        $centerId = Str::ulid()->toString();
        $startDate = Carbon::create(2024, 10, 5, 8, 0, 0); // A Saturday at 8:00 AM
        $expectedNextDate = Carbon::create(2024, 10, 6, 0, 0); // The following Sunday

        $vaccinationCenter = new VaccinationCenter([
            'id' => $centerId,
            'name' => 'Test Center',
            'daily_capacity' => 100,
        ]);

        $this->mockRepository->shouldReceive('getVaccinationCenter')
            ->with($centerId)
            ->andReturn($vaccinationCenter);

        $this->mockRepository->shouldReceive('getVaccinationCountsByDateRange')
            ->with($centerId, Mockery::type(Carbon::class), Mockery::type(Carbon::class))
            ->andReturn(collect([
                '2024-01-08' => (object) ['scheduled_count' => 50],  // Monday - Available
                '2024-01-09' => (object) ['scheduled_count' => 100], // Tuesday - Full
                '2024-01-10' => (object) ['scheduled_count' => 75],  // Wednesday - Available
            ]));

        // Act
        $result = $this->service->getNextAvailableVaccinationDate($centerId, $startDate);

        // Assert
        $this->assertNotNull($result);
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertTrue($expectedNextDate->equalTo($result->toDateString()), "Expected {$expectedNextDate->toDateString()}, but got {$result->toDateString()}");
    }

    public function testGetNextAvailableVaccinationDateWhenStartTimeIsAfter9AM()
    {
        // Arrange
        $centerId = Str::ulid()->toString();
        $startDate = Carbon::create(2024, 1, 1, 10, 0, 0); // A Monday at 10:00 AM (after 9 AM)
        $expectedNextDate = Carbon::create(2024, 1, 2, 0, 0); // The next day (Tuesday)

        $vaccinationCenter = new VaccinationCenter([
            'id' => $centerId,
            'name' => 'Test Center',
            'daily_capacity' => 100,
        ]);

        $this->mockRepository->shouldReceive('getVaccinationCenter')
            ->with($centerId)
            ->andReturn($vaccinationCenter);

        $this->mockRepository->shouldReceive('getVaccinationCountsByDateRange')
            ->with($centerId, Mockery::type(Carbon::class), Mockery::type(Carbon::class))
            ->andReturn(collect([
                '2024-01-01' => (object) ['scheduled_count' => 100], // Monday - Full (but after 9 AM, so skipped)
                '2024-01-02' => (object) ['scheduled_count' => 50],  // Tuesday - Available
                '2024-01-03' => (object) ['scheduled_count' => 100], // Wednesday - Full
            ]));

        // Act
        $result = $this->service->getNextAvailableVaccinationDate($centerId, $startDate);

        // Assert
        $this->assertNotNull($result);
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertTrue($expectedNextDate->equalTo($result->toDateString()), "Expected {$expectedNextDate->toDateString()}, but got {$result->toDateString()}");
    }

    public function testGetNextAvailableVaccinationDateWhenAllDatesAreFull()
    {
        // Arrange
        $centerId = Str::ulid()->toString();
        $startDate = Carbon::create(2024, 1, 1, 8, 0, 0); // A Monday at 8:00 AM

        $vaccinationCenter = new VaccinationCenter([
            'id' => $centerId,
            'name' => 'Test Center',
            'daily_capacity' => 100,
        ]);

        $this->mockRepository->shouldReceive('getVaccinationCenter')
            ->with($centerId)
            ->andReturn($vaccinationCenter);

        // Mock a scenario where all dates are at full capacity
        $fullDates = collect();
        $currentDate = $startDate->copy();
        $endDate = $currentDate->copy()->addDays((int) env('VACCINE_SCHEDULE_WINDOW_DAYS', 90));

        while ($currentDate <= $endDate) {
            if ($currentDate->dayOfWeek !== CarbonInterface::FRIDAY && $currentDate->dayOfWeek !== CarbonInterface::SATURDAY && $currentDate->dayOfWeek !== CarbonInterface::SUNDAY) {
                $fullDates[$currentDate->toDateString()] = (object) ['scheduled_count' => 100]; // Full capacity
            }
            $currentDate->addDay();
        }

        $this->mockRepository->shouldReceive('getVaccinationCountsByDateRange')
            ->with($centerId, Mockery::type(Carbon::class), Mockery::type(Carbon::class))
            ->andReturn($fullDates);

        // Act
        $result = $this->service->getNextAvailableVaccinationDate($centerId, $startDate);

        // Assert
        $this->assertNotNull($result);
    }

    public function testGetNextAvailableVaccinationDateWithSomeFullDays()
    {
        // Arrange
        $centerId = Str::ulid()->toString();
        $startDate = Carbon::create(2024, 1, 1, 8, 0, 0); // A Monday at 8:00 AM

        $vaccinationCenter = new VaccinationCenter([
            'id' => $centerId,
            'name' => 'Test Center',
            'daily_capacity' => 100,
        ]);

        $this->mockRepository->shouldReceive('getVaccinationCenter')
            ->with($centerId)
            ->andReturn($vaccinationCenter);

        // Mock a scenario where some dates are at full capacity, but not all
        $dates = collect([
            '2024-01-01' => (object) ['scheduled_count' => 100], // Monday - Full
            '2024-01-02' => (object) ['scheduled_count' => 100], // Tuesday - Full
            '2024-01-03' => (object) ['scheduled_count' => 100], // Wednesday - Full
            '2024-01-04' => (object) ['scheduled_count' => 50],  // Thursday - Available
            '2024-01-05' => (object) ['scheduled_count' => 0],   // Friday - Skipped
            '2024-01-06' => (object) ['scheduled_count' => 0],   // Saturday - Skipped
            '2024-01-07' => (object) ['scheduled_count' => 0],   // Sunday - Skipped
            '2024-01-08' => (object) ['scheduled_count' => 100], // Monday - Full
        ]);

        $this->mockRepository->shouldReceive('getVaccinationCountsByDateRange')
            ->with($centerId, Mockery::type(Carbon::class), Mockery::type(Carbon::class))
            ->andReturn($dates);

        // Act
        $result = $this->service->getNextAvailableVaccinationDate($centerId, $startDate);

        // Assert
        $this->assertNotNull($result);
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2024-01-04', $result->toDateString(), "Expected 2024-01-04, but got {$result->toDateString()}");
    }

    public function testGetNextAvailableVaccinationDateNearEndOfSchedulingWindow()
    {
        // Arrange
        $centerId = Str::ulid()->toString();
        $scheduleWindowDays = 90;
        $startDate = Carbon::create(2025, 1, 1, 8, 0, 0); // Wednesday, start of the year, before 9 AM
        $endDate = $startDate->copy()->addDays($scheduleWindowDays - 1);

        $vaccinationCenter = new VaccinationCenter([
            'id' => $centerId,
            'name' => 'Test Center',
            'daily_capacity' => 100,
        ]);

        $this->mockRepository->shouldReceive('getVaccinationCenter')
            ->with($centerId)
            ->andReturn($vaccinationCenter);

        // Mock scenario: all dates are full except the last valid day in the window
        $dates = collect();
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            if (! in_array($currentDate->dayOfWeek, [CarbonInterface::FRIDAY, CarbonInterface::SATURDAY])) {
                $scheduledCount = ($currentDate->equalTo($endDate)) ? 0 : 100; // Only the last day is available
                $dates[$currentDate->toDateString()] = (object) ['scheduled_count' => $scheduledCount];
            }
            $currentDate->addDay();
        }

        $this->mockRepository->shouldReceive('getVaccinationCountsByDateRange')
            ->with($centerId, Mockery::type(Carbon::class), Mockery::type(Carbon::class))
            ->andReturn($dates);

        // Act
        $result = $this->service->getNextAvailableVaccinationDate($centerId, $startDate);

        // Assert
        $this->assertNotNull($result);
        $this->assertInstanceOf(Carbon::class, $result);

        // Find the last non-Friday, non-Saturday date in the window
        $expectedDate = $endDate->copy();
        while (in_array($expectedDate->dayOfWeek, [CarbonInterface::FRIDAY, CarbonInterface::SATURDAY])) {
            $expectedDate->subDay();
        }

        $this->assertTrue($expectedDate->equalTo($result), "Expected {$expectedDate->toDateString()}, but got {$result->toDateString()}");
    }

    public function testGetNextAvailableVaccinationDateWithNoRecords()
    {
        // Arrange
        $centerId = Str::ulid()->toString();
        $startDate = Carbon::create(2025, 1, 1, 8, 0, 0); // Wednesday, start of the year, before 9 AM

        $vaccinationCenter = new VaccinationCenter([
            'id' => $centerId,
            'name' => 'Test Center',
            'daily_capacity' => 100,
        ]);

        $this->mockRepository->shouldReceive('getVaccinationCenter')
            ->with($centerId)
            ->andReturn($vaccinationCenter);

        // Mock an empty collection to simulate no records
        $this->mockRepository->shouldReceive('getVaccinationCountsByDateRange')
            ->with($centerId, Mockery::type(Carbon::class), Mockery::type(Carbon::class))
            ->andReturn(collect());

        // Act
        $result = $this->service->getNextAvailableVaccinationDate($centerId, $startDate);

        // Assert
        $this->assertNotNull($result);
        $this->assertInstanceOf(Carbon::class, $result);

        // The result should be the start date, adjusted for weekends if necessary
        $expectedDate = $startDate->copy();
        while (in_array($expectedDate->dayOfWeek, [CarbonInterface::FRIDAY, CarbonInterface::SATURDAY, CarbonInterface::SUNDAY])) {
            $expectedDate->addDay();
        }

        $this->assertTrue($expectedDate->equalTo($result), "Expected {$expectedDate->toDateString()}, but got {$result->toDateString()}");
    }

    public function testSuccessfulRegistration()
    {
        $center = VaccinationCenter::factory()->create(['daily_capacity' => 10]);
        $nextAvailableDate = Carbon::tomorrow();

        $this->mockRepository->shouldReceive('getVaccinationCenter')
            ->once()
            ->with($center->id)
            ->andReturn($center);

        $this->mockRepository->shouldReceive('getVaccinationCountsByDateRange')
            ->once()
            ->with($center->id, Mockery::type(Carbon::class), Mockery::type(Carbon::class))
            ->andReturn(collect([$nextAvailableDate->toDateString() => (object) ['scheduled_count' => 0]]));

        $this->mockRepository->shouldReceive('register')
            ->once()
            ->andReturn([
                'user' => User::factory()->make(),
                'vaccination' => Vaccination::factory()->make([
                    'vaccination_center_id' => $center->id,
                    'vaccination_date' => $nextAvailableDate,
                    'status' => VaccinationStatus::scheduled->value,
                ]),
            ]);

        $result = $this->service->register([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'birth_date' => '1990-01-01',
            'nid' => '1234567890',
            'phone_number' => '1234567890',
            'vaccination_center_id' => $center->id,
        ]);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(201, $result->getStatusCode());

        $content = json_decode($result->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('success', $content['data']);
        $this->assertTrue($content['data']['success']);
        $this->assertArrayHasKey('user', $content['data']);
        $this->assertArrayHasKey('vaccination', $content['data']);
        $this->assertEquals('Vaccine registration is Successful.', $content['message']);
    }
}
