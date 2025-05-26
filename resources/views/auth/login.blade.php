@extends('layouts.app2')

@section('content')
                
                <div class="geex-content__authentication__content__wrapper">
                    <div class="geex-content__authentication__content__logo">
                        <a href="index.html">
                            <img class="logo-lite" src="{{asset('assets/img/wifi-logo.png')}}" alt="logo">
                            <img class="logo-dark" src="{{asset('assets/img/wifi-logo.png')}}" alt="logo">
                        </a>
                    </div>
                    <form id="signInForm" method="POST" action="{{ route('login') }}" class="geex-content__authentication__form">
                         @csrf
                        <h2 class="geex-content__authentication__title">{{ __('Connexion ') }} 👋</h2>
                        <div class="geex-content__authentication__form-group">
                            <label for="emailSignIn">{{ __('Adresse email') }}</label>
                            <input type="email" id="emailSignIn" class="form-control @error('email') is-invalid @enderror" placeholder="Entrez votre email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            <i class="uil-envelope"></i>
                            @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                        </div>
                        <div class="geex-content__authentication__form-group">
                            <div class="geex-content__authentication__label-wrapper">
                                <label for="loginPassword">{{ __('Mot de passe') }}</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}">{{ __('Mot de passe oublié?') }}</a>
                                @endif
                                
                            </div>
                            <input type="password" id="loginPassword" placeholder="Mot de passe" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                            <i class="uil-eye toggle-password-type"></i>
                             @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                        </div>
                        <div class="geex-content__authentication__form-group custom-checkbox">
                            <input type="checkbox" class="geex-content__authentication__checkbox-input" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="geex-content__authentication__checkbox-label" for="rememberMe">{{ __('Souviens-toi de moi') }}</label>
                        </div>
                        <button type="submit" class="geex-content__authentication__form-submit">{{ __('Se connecter') }}</button>

                    </form>
                </div>


@endsection
