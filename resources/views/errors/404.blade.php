@extends('layouts.frontend')

@section('title', 'Page Not Found - 404')

@push('styles')
<style>
  .error-hero {
    background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
  }

  .error-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('{{ asset("frontEnd/images/atv-trial.svg") }}') no-repeat center;
    background-size: 300px;
    opacity: 0.1;
    animation: float 6s ease-in-out infinite;
  }

  @keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
  }

  .error-content {
    position: relative;
    z-index: 2;
  }

  .error-number {
    font-size: 8rem;
    font-weight: 900;
    line-height: 1;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
  }

  .error-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
  }

  .error-message {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
  }

  .error-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
  }

  .btn-home {
    background-color: white;
    color: #ff6600;
    border: 2px solid white;
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
  }

  .btn-home:hover {
    background-color: transparent;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  }

  .btn-back {
    background-color: transparent;
    color: white;
    border: 2px solid white;
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
  }

  .btn-back:hover {
    background-color: white;
    color: #ff6600;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  }

  .error-features {
    padding: 4rem 0;
    background-color: #f8f9fa;
  }

  .feature-card {
    text-align: center;
    padding: 2rem 1rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: transform 0.3s ease;
    height: 100%;
  }

  .feature-card:hover {
    transform: translateY(-5px);
  }

  .feature-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, #ff6600, #ff8533);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
  }

  .feature-title {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
  }

  .feature-text {
    color: #666;
    line-height: 1.6;
  }

  @media (max-width: 768px) {
    .error-number {
      font-size: 6rem;
    }

    .error-title {
      font-size: 2rem;
    }

    .error-message {
      font-size: 1rem;
    }

    .error-actions {
      flex-direction: column;
      align-items: center;
    }

    .btn-home, .btn-back {
      width: 200px;
    }
  }
</style>
@endpush

@section('content')
  <div class="error-hero">
    <div class="error-content">
      <div class="error-number">404</div>
      <h1 class="error-title">Oops! Page Not Found</h1>
      <p class="error-message">
        The adventure you're looking for seems to have taken a detour!
        The page you're trying to reach doesn't exist or has been moved to a different location.
      </p>
      <div class="error-actions">
        <a href="{{ url('/') }}" class="btn-home">
          <i class="bi  bi-home me-2"></i>Go Home
        </a>
        <a href="javascript:history.back()" class="btn-back">
          <i class="bi  bi-arrow-left me-2"></i>Go Back
        </a>
      </div>
    </div>
  </div>

  <section class="error-features">
    <div class="container">
      <div class="row text-center mb-5">
        <div class="col-12">
          <h2 class="display-5 fw-bold text-dark mb-3">While You're Here...</h2>
          <p class="lead text-muted">Explore our exciting adventures and activities</p>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-4 col-md-6">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="bi  bi-motorcycle"></i>
            </div>
            <h3 class="feature-title">ATV Adventures</h3>
            <p class="feature-text">
              Experience the thrill of off-road ATV riding through challenging terrains and scenic trails.
            </p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="bi  bi-bow-arrow"></i>
            </div>
            <h3 class="feature-title">Archery & Target</h3>
            <p class="feature-text">
              Test your skills with traditional archery and target practice in a safe, controlled environment.
            </p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="bi  bi-mountain"></i>
            </div>
            <h3 class="feature-title">Rock Climbing</h3>
            <p class="feature-text">
              Scale challenging rock faces with expert instructors and professional safety equipment.
            </p>
          </div>
        </div>
      </div>

      <div class="row mt-5 text-center">
        <div class="col-12">
          <a href="{{ url('/adventure') }}" class="btn btn-lg jatio-bg-color text-white px-5 py-3">
            <i class="bi  bi-compass me-2"></i>Explore All Adventures
          </a>
        </div>
      </div>
    </div>
  </section>
@endsection
