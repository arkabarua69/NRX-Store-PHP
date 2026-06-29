<?php

namespace Tests\Unit\Filters;

use App\Filters\Components\Status;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Tests\TestCase;

class StatusFilterTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_applies_status_filter_when_param_present(): void
    {
        $filter = new Status();

        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('where')
            ->once()
            ->with('status', 'completed')
            ->andReturnSelf();

        $content = [
            'builder' => $builder,
            'params' => ['status' => 'completed'],
        ];

        $next = function ($content) {
            return $content;
        };

        $result = $filter->handle($content, $next);

        $this->assertSame($builder, $result['builder']);
    }

    public function test_skips_filter_when_status_param_absent(): void
    {
        $filter = new Status();

        $builder = Mockery::mock(Builder::class);
        $builder->shouldNotReceive('where');

        $content = [
            'builder' => $builder,
            'params' => [],
        ];

        $next = function ($content) {
            return $content;
        };

        $result = $filter->handle($content, $next);

        $this->assertSame($builder, $result['builder']);
    }

    public function test_passes_content_to_next_closure(): void
    {
        $filter = new Status();

        $builder = Mockery::mock(Builder::class);

        $content = [
            'builder' => $builder,
            'params' => [],
        ];

        $nextCalled = false;
        $next = function ($content) use (&$nextCalled) {
            $nextCalled = true;
            return $content;
        };

        $filter->handle($content, $next);

        $this->assertTrue($nextCalled);
    }
}
