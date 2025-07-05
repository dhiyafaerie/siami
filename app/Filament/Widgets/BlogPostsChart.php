<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class BlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Current Month',
                    'data' => [65, 59, 90, 81, 56, 55, 40],
                    'backgroundColor' => 'rgba(99, 102, 241, 0.2)',
                    'borderColor' => 'rgba(99, 102, 241, 1)',
                    'pointBackgroundColor' => 'rgba(99, 102, 241, 1)',
                    'pointBorderColor' => '#fff',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => 'rgba(99, 102, 241, 1)',
                ],
                [
                    'label' => 'Previous Month',
                    'data' => [28, 48, 40, 19, 96, 27, 100],
                    'backgroundColor' => 'rgba(255, 159, 64, 0.2)',
                    'borderColor' => 'rgba(255, 159, 64, 1)',
                    'pointBackgroundColor' => 'rgba(255, 159, 64, 1)',
                    'pointBorderColor' => '#fff',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => 'rgba(255, 159, 64, 1)',
                ],
            ],
            'labels' => [
                'Communication', 
                'Technical Skills', 
                'Problem Solving', 
                'Teamwork', 
                'Productivity', 
                'Creativity', 
                'Adaptability'
            ],
        ];
    }

    protected function getType(): string
    {
        return 'radar';
    }
}
