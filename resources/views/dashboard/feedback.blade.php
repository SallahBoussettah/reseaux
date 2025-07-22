@extends('layouts.dashboard')

@section('title', 'Feedback Dashboard')

@section('head')
<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
<style>
    /* Feedback dashboard specific styles */
    .geex-content-wrapper {
        padding: 20px;
        width: 100%;
    }
    
    .feedback-container {
        width: 100%;
    }
    
    /* Header styling */
    .feedback-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding: 0;
    }
    
    .feedback-header-left {
        display: flex;
        flex-direction: column;
    }
    
    .feedback-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
    }
    
    .feedback-subtitle {
        font-size: 0.95rem;
        color: #6B7280;
        margin-top: 0.25rem;
    }
    
    /* Stats cards */
    .feedback-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
        width: 100%;
    }
    
    .stat-card {
        border-radius: 12px;
        padding: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: transform 0.2s, box-shadow 0.2s;
        width: 100%;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    .stat-card.primary {
        background: linear-gradient(135deg, #3B82F6, #2563EB);
    }
    
    .stat-card.success {
        background: linear-gradient(135deg, #10B981, #059669);
    }
    
    .stat-card.info {
        background: linear-gradient(135deg, #06B6D4, #0891B2);
    }
    
    .stat-card.warning {
        background: linear-gradient(135deg, #F59E0B, #D97706);
    }
    
    .stat-card.danger {
        background: linear-gradient(135deg, #EF4444, #DC2626);
    }
    
    .stat-card.purple {
        background: linear-gradient(135deg, #8B5CF6, #7C3AED);
    }
    
    .stat-content {
        display: flex;
        flex-direction: column;
        z-index: 1;
    }
    
    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
    }
    
    .satisfaction-rate {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
    }
    
    .rating {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
    }
    
    .stars {
        color: #FBBF24;
    }
    
    .stat-label {
        font-size: 0.875rem;
        margin-top: 0.25rem;
        opacity: 0.9;
    }
    
    .stat-icon {
        font-size: 2rem;
        opacity: 0.8;
        z-index: 1;
    }
    
    /* Table styling */
    .feedback-table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        overflow: hidden;
        margin-bottom: 2rem;
        width: 100%;
    }
    
    .feedback-table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #E5E7EB;
    }
    
    .table-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .table-title i {
        color: #3B82F6;
        font-size: 1.25rem;
    }
    
    .table-title h3 {
        font-size: 1.125rem;
        font-weight: 600;
        margin: 0;
        color: #374151;
    }
    
    .feedback-cards-container {
        padding: 1.5rem;
    }
    
    .feedback-card {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #E5E7EB;
    }
    
    .feedback-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    .feedback-card .ratings {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin: 1rem 0;
    }
    
    .feedback-card .rating-item {
        background-color: #F9FAFB;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        border: 1px solid #E5E7EB;
        transition: all 0.2s;
    }
    
    .feedback-card .rating-item:hover {
        border-color: #3B82F6;
        background-color: #F0F7FF;
    }
    
    .feedback-card .rating-label {
        font-size: 0.8rem;
        color: #6B7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    
    .feedback-card .rating-value {
        display: block;
        font-size: 1.2rem;
        color: #1F2937;
        font-weight: 600;
    }
    
    .feedback-card .rating-stars {
        color: #FBBF24;
    }
    
    .feedback-card .comments-section {
        background-color: #F9FAFB;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
        border: 1px solid #E5E7EB;
    }
    
    .feedback-card .comments-title {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .feedback-card .comments-title i {
        color: #3B82F6;
    }
    
    .feedback-card .comments-text {
        color: #4B5563;
        font-size: 0.95rem;
        line-height: 1.6;
    }
    
    .feedback-meta {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        color: #6B7280;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #E5E7EB;
    }
    
    .satisfaction-indicator {
        display: inline-block;
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 50%;
        margin-right: 0.5rem;
    }
    
    .satisfaction-indicator.satisfied {
        background-color: #10B981;
    }
    
    .satisfaction-indicator.unsatisfied {
        background-color: #EF4444;
    }
    
    .feedback-user-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .feedback-user {
        display: flex;
        align-items: center;
        font-weight: 600;
        color: #1F2937;
    }
    
    .feedback-user i {
        margin-right: 0.5rem;
        color: #3B82F6;
    }
    
    .feedback-date {
        color: #6B7280;
        font-size: 0.9rem;
    }
    
    .pagination-container {
        display: flex;
        justify-content: center;
        margin-top: 1.5rem;
    }
    
    .text-success {
        color: #10B981 !important;
    }
    
    .text-danger {
        color: #EF4444 !important;
    }
    
    .text-muted {
        color: #6B7280 !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .feedback-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .feedback-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .feedback-stats {
            grid-template-columns: 1fr;
        }
        
        .feedback-card .ratings {
            flex-direction: column;
            gap: 0.75rem;
        }
    }
</style>
@endsection

@section('content')
<div style="padding: 20px; width: 100%;">
    <!-- Header section -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding: 0;">
        <div style="display: flex; flex-direction: column;">
            <h2 style="font-size: 1.75rem; font-weight: 600; color: #1F2937; margin: 0;">{{ __('Feedback Dashboard') }}</h2>
            <p style="font-size: 0.95rem; color: #6B7280; margin-top: 0.25rem;">{{ __('View and analyze customer feedback') }}</p>
        </div>
    </div>
    
    <!-- Statistics Row -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="border-radius: 12px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); background: linear-gradient(135deg, #3B82F6, #2563EB);">
            <div style="display: flex; flex-direction: column; z-index: 1;">
                <div style="font-size: 1.75rem; font-weight: 700; line-height: 1;">{{ $averages['total'] }}</div>
                <div style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">{{ __('Total Feedback') }}</div>
            </div>
            <div style="font-size: 2rem; opacity: 0.8; z-index: 1;">
                <i class="uil uil-comment-alt-lines"></i>
            </div>
        </div>
        
        <div style="border-radius: 12px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); background: linear-gradient(135deg, #10B981, #059669);">
            <div style="display: flex; flex-direction: column; z-index: 1;">
                <div style="font-size: 1.75rem; font-weight: 700; line-height: 1;">{{ round($averages['satisfaction_rate']) }}%</div>
                <div style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">{{ __('Satisfaction Rate') }}</div>
            </div>
            <div style="font-size: 2rem; opacity: 0.8; z-index: 1;">
                <i class="uil uil-smile"></i>
            </div>
        </div>
        
        <div style="border-radius: 12px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); background: linear-gradient(135deg, #06B6D4, #0891B2);">
            <div style="display: flex; flex-direction: column; z-index: 1;">
                <div style="font-size: 1.75rem; font-weight: 700; line-height: 1;">
                    {{ number_format($averages['wifi'], 1) }}
                    <span style="color: #FBBF24;">★</span>
                </div>
                <div style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">{{ __('WiFi Rating') }}</div>
            </div>
            <div style="font-size: 2rem; opacity: 0.8; z-index: 1;">
                <i class="uil uil-wifi"></i>
            </div>
        </div>
        
        <div style="border-radius: 12px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); background: linear-gradient(135deg, #F59E0B, #D97706);">
            <div style="display: flex; flex-direction: column; z-index: 1;">
                <div style="font-size: 1.75rem; font-weight: 700; line-height: 1;">
                    {{ number_format($averages['hotel'], 1) }}
                    <span style="color: #FBBF24;">★</span>
                </div>
                <div style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">{{ __('Hotel Rating') }}</div>
            </div>
            <div style="font-size: 2rem; opacity: 0.8; z-index: 1;">
                <i class="uil uil-building"></i>
            </div>
        </div>
    </div>
    
    <!-- Additional Stats Row -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="border-radius: 12px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); background: linear-gradient(135deg, #EF4444, #DC2626);">
            <div style="display: flex; flex-direction: column; z-index: 1;">
                <div style="font-size: 1.75rem; font-weight: 700; line-height: 1;">
                    {{ number_format($averages['room'], 1) }}
                    <span style="color: #FBBF24;">★</span>
                </div>
                <div style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">{{ __('Room Rating') }}</div>
            </div>
            <div style="font-size: 2rem; opacity: 0.8; z-index: 1;">
                <i class="uil uil-bed"></i>
            </div>
        </div>
        
        <div style="border-radius: 12px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); background: linear-gradient(135deg, #8B5CF6, #7C3AED);">
            <div style="display: flex; flex-direction: column; z-index: 1;">
                <div style="font-size: 1.75rem; font-weight: 700; line-height: 1;">
                    {{ number_format($averages['service'], 1) }}
                    <span style="color: #FBBF24;">★</span>
                </div>
                <div style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">{{ __('Service Rating') }}</div>
            </div>
            <div style="font-size: 2rem; opacity: 0.8; z-index: 1;">
                <i class="uil uil-users-alt"></i>
            </div>
        </div>
        
        @if(isset($averages['food']) && $averages['food'] > 0)
        <div style="border-radius: 12px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); background: linear-gradient(135deg, #3B82F6, #2563EB);">
            <div style="display: flex; flex-direction: column; z-index: 1;">
                <div style="font-size: 1.75rem; font-weight: 700; line-height: 1;">
                    {{ number_format($averages['food'], 1) }}
                    <span style="color: #FBBF24;">★</span>
                </div>
                <div style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">{{ __('Food Rating') }}</div>
            </div>
            <div style="font-size: 2rem; opacity: 0.8; z-index: 1;">
                <i class="uil uil-restaurant"></i>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Feedback List -->
    <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); overflow: hidden; margin-bottom: 2rem; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #E5E7EB;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <i class="uil uil-comments-alt" style="color: #3B82F6; font-size: 1.25rem;"></i>
                <h3 style="font-size: 1.125rem; font-weight: 600; margin: 0; color: #374151;">{{ __('Recent Feedback') }}</h3>
            </div>
        </div>
        <div style="padding: 1.5rem;">
            @foreach($feedback as $item)
            <div style="background-color: white; border-radius: 10px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); padding: 1.5rem; margin-bottom: 1rem; transition: transform 0.2s, box-shadow 0.2s; border: 1px solid #E5E7EB;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; font-weight: 600; color: #1F2937;">
                        @if($item->is_satisfied === true)
                        <span style="display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 50%; margin-right: 0.5rem; background-color: #10B981;" title="{{ __('Satisfied') }}"></span>
                        @elseif($item->is_satisfied === false)
                        <span style="display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 50%; margin-right: 0.5rem; background-color: #EF4444;" title="{{ __('Unsatisfied') }}"></span>
                        @endif
                        <i class="uil uil-user" style="margin-right: 0.5rem; color: #3B82F6;"></i>
                        {{ $item->client->full_name ?? $item->email ?? __('Anonymous') }}
                    </div>
                    <div style="color: #6B7280; font-size: 0.9rem;">{{ $item->created_at->format('M d, Y') }}</div>
                </div>
                
                <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin: 1rem 0;">
                    @if($item->wifi_rating)
                    <div style="background-color: #F9FAFB; border-radius: 8px; padding: 0.75rem 1rem; border: 1px solid #E5E7EB; transition: all 0.2s;">
                        <span style="font-size: 0.8rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; display: block;">{{ __('WiFi') }}</span>
                        <span style="display: block; font-size: 1.2rem; color: #1F2937; font-weight: 600; text-align: center;">{{ $item->wifi_rating }}</span>
                    </div>
                    @endif
                    
                    @if($item->hotel_rating)
                    <div style="background-color: #F9FAFB; border-radius: 8px; padding: 0.75rem 1rem; border: 1px solid #E5E7EB; transition: all 0.2s;">
                        <span style="font-size: 0.8rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; display: block;">{{ __('Hotel') }}</span>
                        <span style="display: block; font-size: 1.2rem; color: #1F2937; font-weight: 600; text-align: center;">{{ $item->hotel_rating }}</span>
                    </div>
                    @endif
                    
                    @if($item->room_rating)
                    <div style="background-color: #F9FAFB; border-radius: 8px; padding: 0.75rem 1rem; border: 1px solid #E5E7EB; transition: all 0.2s;">
                        <span style="font-size: 0.8rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; display: block;">{{ __('Room') }}</span>
                        <span style="display: block; font-size: 1.2rem; color: #1F2937; font-weight: 600; text-align: center;">{{ $item->room_rating }}</span>
                    </div>
                    @endif
                    
                    @if($item->service_rating)
                    <div style="background-color: #F9FAFB; border-radius: 8px; padding: 0.75rem 1rem; border: 1px solid #E5E7EB; transition: all 0.2s;">
                        <span style="font-size: 0.8rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; display: block;">{{ __('Service') }}</span>
                        <span style="display: block; font-size: 1.2rem; color: #1F2937; font-weight: 600; text-align: center;">{{ $item->service_rating }}</span>
                    </div>
                    @endif
                    
                    @if($item->food_rating)
                    <div style="background-color: #F9FAFB; border-radius: 8px; padding: 0.75rem 1rem; border: 1px solid #E5E7EB; transition: all 0.2s;">
                        <span style="font-size: 0.8rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; display: block;">{{ __('Food') }}</span>
                        <span style="display: block; font-size: 1.2rem; color: #1F2937; font-weight: 600; text-align: center;">{{ $item->food_rating }}</span>
                    </div>
                    @endif
                </div>
                
                @if($item->comments)
                <div style="background-color: #F9FAFB; border-radius: 8px; padding: 1rem; margin-top: 1rem; border: 1px solid #E5E7EB;">
                    <h5 style="font-size: 0.9rem; font-weight: 600; margin-bottom: 0.75rem; color: #374151; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="uil uil-comment-alt" style="color: #3B82F6;"></i> 
                        {{ __('Comments') }}
                    </h5>
                    <p style="color: #4B5563; font-size: 0.95rem; line-height: 1.6;">{{ $item->comments }}</p>
                </div>
                @endif
                
                @if($item->suggestion)
                <div style="background-color: #F9FAFB; border-radius: 8px; padding: 1rem; margin-top: 1rem; border: 1px solid #E5E7EB;">
                    <h5 style="font-size: 0.9rem; font-weight: 600; margin-bottom: 0.75rem; color: #374151; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="uil uil-lightbulb-alt" style="color: #3B82F6;"></i> 
                        {{ __('Suggestions') }}
                    </h5>
                    <p style="color: #4B5563; font-size: 0.95rem; line-height: 1.6;">{{ $item->suggestion }}</p>
                </div>
                @endif
                
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #6B7280; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #E5E7EB;">
                    <div>
                        {{ __('Visit Again') }}: 
                        @if($item->visit_again === true)
                        <span style="color: #10B981;"><i class="uil uil-check-circle"></i> {{ __('Yes') }}</span>
                        @elseif($item->visit_again === false)
                        <span style="color: #EF4444;"><i class="uil uil-times-circle"></i> {{ __('No') }}</span>
                        @else
                        <span style="color: #6B7280;"><i class="uil uil-question-circle"></i> {{ __('Not specified') }}</span>
                        @endif
                    </div>
                    
                    <div>
                        <i class="uil uil-location-point"></i> {{ __('Submitted from') }}: {{ $item->ip_address ?? __('Unknown') }}
                    </div>
                </div>
            </div>
            @endforeach
            
            @if(count($feedback) === 0)
            <div style="text-align: center; padding: 3rem 0;">
                <i class="uil uil-comment-alt-slash" style="font-size: 3rem; color: #6B7280;"></i>
                <p style="color: #6B7280; margin-top: 1rem;">{{ __('No feedback submissions yet.') }}</p>
            </div>
            @endif
            
            <div style="display: flex; justify-content: center; margin-top: 1.5rem;">
                {{ $feedback->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 