@extends('layouts.dashboard')

@section('title', 'Clients')

@section('head')
<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
<style>
    /* Client page specific styles */
    .clients-container {
        padding: 0;
        width: 100%;
    }
    
    /* Header styling */
    .clients-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding: 0;
    }
    
    .clients-header-left {
        display: flex;
        flex-direction: column;
    }
    
    .clients-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #1F2937;
        margin: 0;
    }
    
    .clients-subtitle {
        font-size: 0.95rem;
        color: #6B7280;
        margin-top: 0.25rem;
    }
    
    .clients-header-right {
        display: flex;
        align-items: center;
    }
    
    .clients-actions {
        display: flex;
        gap: 1rem;
        align-items: center;
    }
    
    .clients-action-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #3B82F6;
        color: white !important;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s;
        text-decoration: none;
    }
    
    .clients-action-btn:hover {
        background: #2563EB;
        color: white !important;
        text-decoration: none;
    }
    
    .clients-search {
        position: relative;
        width: 250px;
    }
    
    .clients-search input {
        width: 100%;
        padding: 0.5rem 1rem 0.5rem 2.5rem;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        font-size: 0.875rem;
    }
    
    .clients-search i {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9CA3AF;
    }
    
    /* Alert styling */
    .clients-alerts {
        margin-bottom: 1.5rem;
    }
    
    .alert {
        display: flex;
        align-items: center;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    .alert i {
        font-size: 1.25rem;
        margin-right: 0.75rem;
    }
    
    .alert-success {
        background-color: rgba(16, 185, 129, 0.1);
        border-left: 4px solid #10B981;
        color: #065F46;
    }
    
    .alert-danger {
        background-color: rgba(244, 63, 94, 0.1);
        border-left: 4px solid #F43F5E;
        color: #9F1239;
    }
    
    /* Stats cards */
    .clients-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .client-stat-card {
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
    }
    
    .client-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    .client-stat-card.teal {
        background: linear-gradient(135deg, #14B8A6, #0D9488);
    }
    
    .client-stat-card.orange {
        background: linear-gradient(135deg, #F59E0B, #D97706);
    }
    
    .client-stat-card.purple {
        background: linear-gradient(135deg, #8B5CF6, #7C3AED);
    }
    
    .stat-content {
        display: flex;
        flex-direction: column;
    }
    
    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
    }
    
    .stat-label {
        font-size: 0.875rem;
        margin-top: 0.25rem;
        opacity: 0.9;
    }
    
    .stat-icon {
        font-size: 2rem;
        opacity: 0.8;
    }
    
    /* Table styling */
    .clients-table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }
    
    .clients-table-header {
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
    
    .refresh-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border: 1px solid #3B82F6;
        background: white;
        color: #3B82F6;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .refresh-btn:hover {
        background: #3B82F6;
        color: white;
    }
    
    .clients-table-container {
        overflow-x: auto;
    }
    
    .clients-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .clients-table thead {
        background-color: #F9FAFB;
    }
    
    .clients-table th {
        padding: 0.875rem 1.25rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #E5E7EB;
        white-space: nowrap;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .clients-table td {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #E5E7EB;
        color: #374151;
        font-size: 0.875rem;
    }
    
    .clients-table tbody tr:hover {
        background-color: #F9FAFB;
    }
    
    .client-name {
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .client-name i {
        color: #3B82F6;
        font-size: 1rem;
    }
    
    .client-email {
        color: #6B7280;
    }
    
    .client-mac {
        font-family: monospace;
        font-size: 0.85rem;
    }
    
    .device-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem 0.5rem;
        background-color: #F3F4F6;
        border-radius: 4px;
        font-size: 0.75rem;
        color: #374151;
    }
    
    .profile-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .profile-badge.premium {
        background-color: rgba(139, 92, 246, 0.1);
        color: #7C3AED;
    }
    
    .profile-badge.free {
        background-color: rgba(156, 163, 175, 0.1);
        color: #4B5563;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-badge.active {
        background-color: rgba(16, 185, 129, 0.1);
        color: #065F46;
    }
    
    .status-badge.inactive {
        background-color: rgba(244, 63, 94, 0.1);
        color: #9F1239;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        text-decoration: none;
    }
    
    .action-btn.deactivate {
        background-color: #F43F5E;
        color: white !important;
    }
    
    .action-btn.deactivate:hover {
        background-color: #E11D48;
        color: white !important;
        text-decoration: none;
    }
    
    .action-btn.delete {
        background-color: #F59E0B;
        color: white !important;
    }
    
    .action-btn.delete:hover {
        background-color: #D97706;
        color: white !important;
        text-decoration: none;
    }
    
    .status-pill {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-pill.inactive {
        background-color: #E5E7EB;
        color: #4B5563;
    }
    
    .text-center {
        text-align: center;
    }
    
    /* Pagination styling */
    .clients-pagination {
        padding: 1rem 1.5rem;
        border-top: 1px solid #E5E7EB;
    }
    
    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .clients-stats {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 991px) {
        .clients-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .clients-header-right {
            width: 100%;
        }
        
        .clients-actions {
            width: 100%;
            justify-content: space-between;
        }
        
        .clients-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .clients-actions {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .clients-search {
            width: 100%;
        }
    }
    
    @media (max-width: 576px) {
        .clients-stats {
            grid-template-columns: 1fr;
        }
        
        .clients-table-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
    }
</style>
@endsection

@section('content')
<div style="padding: 0; width: 100%;">
    <!-- Header section with improved styling -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding: 0;">
        <div style="display: flex; flex-direction: column;">
            <h2 style="font-size: 1.75rem; font-weight: 600; color: #1F2937; margin: 0;">Clients Management</h2>
            <p style="font-size: 0.95rem; color: #6B7280; margin-top: 0.25rem;">View and manage all registered users</p>
        </div>
        
        <div style="display: flex; align-items: center;">
            <div style="display: flex; gap: 1rem; align-items: center;">
                <a href="{{ route('test.delete.expired') }}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #3B82F6; color: white; border-radius: 8px; font-size: 0.875rem; font-weight: 500; transition: all 0.2s;" onclick="return confirm('Are you sure? This will remove expired users from router and disable them in database.');">
                    <i class="uil uil-users-alt"></i>
                    Process Expired Users
                </a>
                <div style="position: relative; width: 250px;">
                    <input type="text" id="clientSearch" placeholder="Search clients..." style="width: 100%; padding: 0.5rem 1rem 0.5rem 2.5rem; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 0.875rem;">
                    <i class="uil uil-search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #9CA3AF;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert messages -->
    <div style="margin-bottom: 1.5rem;">
        @if(session('success'))
            <div style="display: flex; align-items: center; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; background-color: rgba(16, 185, 129, 0.1); border-left: 4px solid #10B981; color: #065F46;">
                <i class="uil uil-check-circle" style="font-size: 1.25rem; margin-right: 0.75rem;"></i>
                <div>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div style="display: flex; align-items: center; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; background-color: rgba(244, 63, 94, 0.1); border-left: 4px solid #F43F5E; color: #9F1239;">
                <i class="uil uil-exclamation-triangle" style="font-size: 1.25rem; margin-right: 0.75rem;"></i>
                <div>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>

    <!-- Client stats cards -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="border-radius: 12px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); background: linear-gradient(135deg, #14B8A6, #0D9488);">
            <div style="display: flex; flex-direction: column;">
                <div style="font-size: 1.75rem; font-weight: 700; line-height: 1;">{{ $clients->total() }}</div>
                <div style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">Total Clients</div>
            </div>
            <div style="font-size: 2rem; opacity: 0.8;">
                <i class="uil uil-users-alt"></i>
            </div>
        </div>
        
        <div style="border-radius: 12px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); background: linear-gradient(135deg, #F59E0B, #D97706);">
            <div style="display: flex; flex-direction: column;">
                <div style="font-size: 1.75rem; font-weight: 700; line-height: 1;">{{ $clients->where('status', 'active')->count() }}</div>
                <div style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">Active Clients</div>
            </div>
            <div style="font-size: 2rem; opacity: 0.8;">
                <i class="uil uil-user-check"></i>
            </div>
        </div>
        
        <div style="border-radius: 12px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); background: linear-gradient(135deg, #8B5CF6, #7C3AED);">
            <div style="display: flex; flex-direction: column;">
                <div style="font-size: 1.75rem; font-weight: 700; line-height: 1;">{{ $clients->where('profile_type', 'premium_user')->count() }}</div>
                <div style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">Premium Users</div>
            </div>
            <div style="font-size: 2rem; opacity: 0.8;">
                <i class="uil uil-diamond"></i>
            </div>
        </div>
    </div>

    <!-- Table card with improved styling -->
    <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); overflow: hidden;">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #E5E7EB;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <i class="uil uil-list-ul" style="color: #3B82F6; font-size: 1.25rem;"></i>
                <h3 style="font-size: 1.125rem; font-weight: 600; margin: 0; color: #374151;">Client List</h3>
            </div>
            <div>
                <button onclick="location.reload();" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border: 1px solid #3B82F6; background: white; color: #3B82F6; border-radius: 8px; font-size: 0.875rem; font-weight: 500; cursor: pointer;">
                    <i class="uil uil-sync"></i>
                    Refresh
                </button>
            </div>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: #F9FAFB;">
                    <tr>
                        <th style="padding: 0.875rem 1.25rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; white-space: nowrap; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Full Name</th>
                        <th style="padding: 0.875rem 1.25rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; white-space: nowrap; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Email</th>
                        <th style="padding: 0.875rem 1.25rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; white-space: nowrap; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Device Type</th>
                        <th style="padding: 0.875rem 1.25rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; white-space: nowrap; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Premium Expires At</th>
                        <th style="padding: 0.875rem 1.25rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; white-space: nowrap; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Profile Type</th>
                        <th style="padding: 0.875rem 1.25rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #E5E7EB; white-space: nowrap; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($clients as $client)
                <tr class="client-row">
                    <td style="padding: 1rem 1.25rem; border-bottom: 1px solid #E5E7EB; color: #374151; font-size: 0.875rem; font-weight: 500;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="uil uil-user" style="color: #3B82F6; font-size: 1rem;"></i>
                            <span class="client-name">{{ $client->full_name }}</span>
                        </div>
                    </td>
                    <td style="padding: 1rem 1.25rem; border-bottom: 1px solid #E5E7EB; color: #6B7280; font-size: 0.875rem;" class="client-email">
                        @if(strpos($client->email, 'token_user') !== false)
                            <span style="background-color: rgba(139, 92, 246, 0.1); color: #7C3AED; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 500;">Token User</span>
                        @else
                            {{ $client->email }}
                        @endif
                    </td>
                    <td style="padding: 1rem 1.25rem; border-bottom: 1px solid #E5E7EB; color: #374151; font-size: 0.875rem;">
                        <span style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.5rem; background-color: #F3F4F6; border-radius: 4px; font-size: 0.75rem; color: #374151;">
                            <i class="uil {{ $client->device_type == 'mobile' ? 'uil-mobile-android' : 'uil-desktop' }}"></i>
                            {{ ucfirst($client->device_type) }}
                        </span>
                    </td>
                    <td style="padding: 1rem 1.25rem; border-bottom: 1px solid #E5E7EB; color: #374151; font-size: 0.875rem;">{{ $client->premium_expires_at ? $client->premium_expires_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                    <td style="padding: 1rem 1.25rem; border-bottom: 1px solid #E5E7EB; color: #374151; font-size: 0.875rem;">
                        <span style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 500; background-color: {{ $client->profile_type == 'premium_user' ? 'rgba(139, 92, 246, 0.1)' : 'rgba(156, 163, 175, 0.1)' }}; color: {{ $client->profile_type == 'premium_user' ? '#7C3AED' : '#4B5563' }};">
                            {{ $client->profile_type ?? 'free_user' }}
                        </span>
                    </td>
                    <td style="padding: 1rem 1.25rem; border-bottom: 1px solid #E5E7EB; color: #374151; font-size: 0.875rem;">
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            @if ($client->status === 'active')
                            <form action="{{ route('clients.deactivate', $client->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to deactivate this user?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 500; cursor: pointer; transition: all 0.2s; border: none; background-color: #F43F5E; color: white;">
                                    <i class="uil uil-user-times"></i>
                                    Deactivate
                                </button>
                            </form>
                            @elseif ($client->status === 'disabled')
                            <span style="display: inline-block; padding: 0.375rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 500; background-color: #FF9800; color: white;">Disabled</span>
                            @else
                            <span style="display: inline-block; padding: 0.375rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 500; background-color: #E5E7EB; color: #4B5563;">Deactivated</span>
                            @endif
                            
                            <a href="{{ route('schedule.deletion', $client->id) }}" onclick="return confirm('Are you sure you want to schedule this user for removal from router and disabling in 1 minute?');" style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 500; cursor: pointer; transition: all 0.2s; border: none; background-color: #F59E0B; color: white; text-decoration: none;">
                                <i class="uil uil-schedule"></i>
                                Schedule Removal
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #E5E7EB;">
            {{ $clients->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Client search functionality
    const searchInput = document.getElementById('clientSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.client-row');
            
            tableRows.forEach(row => {
                const name = row.querySelector('.client-name').textContent.toLowerCase();
                const email = row.querySelector('.client-email').textContent.toLowerCase();
                const mac = row.querySelector('.client-mac').textContent.toLowerCase();
                
                if (name.includes(searchTerm) || email.includes(searchTerm) || mac.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>
@endsection