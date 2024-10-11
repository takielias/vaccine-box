<?php

namespace Tests\Unit;

use App\Contracts\Repositories\VaccinationStatusRepositoryInterface;
use App\Enums\VaccinationStatus;
use App\Models\User;
use App\Models\Vaccination;
use App\Services\VaccinationStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Mockery;

class VaccinationStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $mockRepository;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = Mockery::mock(VaccinationStatusRepositoryInterface::class);
        $this->service = new VaccinationStatusService($this->mockRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetVaccinationStatusForNotRegisteredUser()
    {
        $nid = '1234567890';

        $this->mockRepository->shouldReceive('getVaccinationStatus')
            ->once()
            ->with($nid)
            ->andReturnNull();

        $result = $this->service->getVaccinationStatus($nid);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(400, $result->getStatusCode());

        $content = json_decode($result->getContent(), true);
        $this->assertFalse($content['data']['success']);
        $this->assertEquals(VaccinationStatus::notRegistered->value, $content['data']['status']);
        $this->assertStringContainsString('You are not registered for vaccination', $content['message']);
    }

    public function testGetVaccinationStatusForScheduledVaccination()
    {
        $nid = '1234567890';
        $user = User::factory()->create(['nid' => $nid]);
        $vaccination = Vaccination::factory()->create([
            'user_id' => $user->id,
            'vaccination_date' => Carbon::tomorrow(),
            'status' => VaccinationStatus::scheduled->value
        ]);

        $this->mockRepository->shouldReceive('getVaccinationStatus')
            ->once()
            ->with($nid)
            ->andReturn($user);

        $result = $this->service->getVaccinationStatus($nid);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());

        $content = json_decode($result->getContent(), true);
        $this->assertTrue($content['data']['success']);
        $this->assertEquals(VaccinationStatus::scheduled->value, $content['data']['status']);
        $this->assertStringContainsString('Your vaccination is scheduled for', $content['message']);
    }

    public function testGetVaccinationStatusForCompletedVaccination()
    {
        $nid = '1234567890';
        $user = User::factory()->create(['nid' => $nid]);
        $vaccination = Vaccination::factory()->create([
            'user_id' => $user->id,
            'vaccination_date' => Carbon::yesterday(),
            'status' => VaccinationStatus::vaccinated->value
        ]);

        $this->mockRepository->shouldReceive('getVaccinationStatus')
            ->once()
            ->with($nid)
            ->andReturn($user);

        $result = $this->service->getVaccinationStatus($nid);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());

        $content = json_decode($result->getContent(), true);
        $this->assertTrue($content['data']['success']);
        $this->assertEquals(VaccinationStatus::vaccinated->value, $content['data']['status']);
        $this->assertStringContainsString('Your vaccination was completed on', $content['message']);
    }

    public function testGetVaccinationStatusForErrorScenario()
    {
        $nid = '1234567890';

        $this->mockRepository->shouldReceive('getVaccinationStatus')
            ->once()
            ->with($nid)
            ->andThrow(new \Exception('Database error'));

        $result = $this->service->getVaccinationStatus($nid);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(500, $result->getStatusCode());

        $content = json_decode($result->getContent(), true);
        $this->assertFalse($content['data']['success']);
        $this->assertNull($content['data']['status']);
        $this->assertEquals('An error occurred while fetching the vaccination status.', $content['message']);
    }
}
