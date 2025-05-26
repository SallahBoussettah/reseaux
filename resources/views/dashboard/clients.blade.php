@extends('layouts.dashboard')

@section('title', 'Clients')

@section('content')

<div class="geex-content__header">
				<div class="geex-content__header__content">
					<h2 class="geex-content__header__title">Clients</h2>
				</div>
				
				<div class="geex-content__header__action">
				 
				    <div class="geex-content__header__action__wrap">
				        <ul class="geex-content__header__quickaction">
				           
				            <li class="geex-content__header__quickaction__item">
				                <a href="#" class="geex-content__header__quickaction__link">
				                    <img class="user-img" src="assets/img/avatar/user.svg" alt="user" />
				                </a>
				                <div class="geex-content__header__popup geex-content__header__popup--author">
				                    <div class="geex-content__header__popup__header">
				                        <div class="geex-content__header__popup__header__img">
				                            <img src="assets/img/avatar/user.svg" alt="user" />
				                        </div>
				                        <div class="geex-content__header__popup__header__content">
				                            <h3 class="geex-content__header__popup__header__title">Mahabub Alam</h3>
				                            <span class="geex-content__header__popup__header__subtitle">CEO, ThemeWant</span>
				                        </div>
				                    </div>
				                    <div class="geex-content__header__popup__content">
				                        <ul class="geex-content__header__popup__items">
				                            <li class="geex-content__header__popup__item">
				                                <a class="geex-content__header__popup__link" href="#">
				                                    <i class="uil uil-user"></i>
				                                    Profile
				                                </a>
				                            </li>
				                            <li class="geex-content__header__popup__item">
				                                <a class="geex-content__header__popup__link" href="#">
				                                    <i class="uil uil-cog"></i>
				                                    Settings
				                                </a>
				                            </li>
				                            <li class="geex-content__header__popup__item">
				                                <a class="geex-content__header__popup__link" href="#">
				                                    <i class="uil uil-dollar-alt"></i>
				                                    Billing
				                                </a>
				                            </li>
				                            <li class="geex-content__header__popup__item">
				                                <a class="geex-content__header__popup__link" href="#">
				                                    <i class="uil uil-users-alt"></i>
				                                    Activity
				                                </a>
				                            </li>
				                            <li class="geex-content__header__popup__item">
				                                <a class="geex-content__header__popup__link" href="#">
				                                    <i class="uil uil-bell"></i>
				                                    Help
				                                </a>
				                            </li>
				                        </ul>
				                    </div>
				                    <div class="geex-content__header__popup__footer">
				                        <a href="#" class="geex-content__header__popup__footer__link">
				                            <i class="uil uil-arrow-up-left"></i>Logout
				                        </a>
				                    </div>
				                </div>
				            </li>
				        </ul>
				    </div>
				</div>
			</div>

			<div class="geex-content__section geex-content__form table-responsive">


                <table class="table-reviews-geex-1">
                    <thead>
                        <tr style="width: 100%;">
		                  	<th>Full Name</th>
			                <th>Email</th>
			                <th>MAC Address</th>
			                <th>Device Type</th>
			                <th>Platform</th>
			                <th>Login Count</th>
			                <th>Premium Expires At</th>
			                <th>Status</th>
			                <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="">
		            @foreach ($clients as $client)
		            <tr>
		                <td>{{ $client->full_name }}</td>
		                <td>{{ $client->email }}</td>
		                <td>{{ $client->mac_address }}</td>
		                <td>{{ ucfirst($client->device_type) }}</td>
		                <td>{{ ucfirst($client->platform) }}</td>
		                <td>{{ $client->login_count }}</td>
		                <td>{{ $client->premium_expires_at ? $client->premium_expires_at->format('Y-m-d') : 'N/A' }}</td>
		                <td>{{ $client->status ? ucfirst($client->status) : 'N/A' }}</td>
		                <td>
		                    @if ($client->status === 'active')
			                <form action="{{ route('clients.deactivate', $client->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to deactivate this user?');">
			                    @csrf
			                    @method('PATCH')
			                    <button type="submit" class="btn btn-danger" style="color: #fff;">Deactivate</button>
			                </form>
			                @else
			                <span class="badge badge-secondary" style="color: #000;">Deactivated</span>
			                @endif
		                </td>
		            </tr>
		            @endforeach
                    </tbody>

                </table>
            {{ $clients->links() }}  <!-- Pagination links -->
			</div>
			
    
@endsection