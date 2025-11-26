@extends('layouts.frontend')

@section('title', 'Server Error - 500')

@push('styles')
<style>
  .error-hero {
    background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
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
    background: url('{{ asset("frontEnd/images/utv-trial.svg") }}') no-repeat center;
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
    color: #dc3545;
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

  .btn-refresh {
    background-color: transparent;
    color: white;
    border: 2px solid white;
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
  }

  .btn-refresh:hover {
    background-color: white;
    color: #dc3545;
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
    background: linear-gradient(135deg, #dc3545, #e74c3c);
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

  .contact-info {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-top: 2rem;
  }

  .contact-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
  }

  .contact-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #dc3545, #e74c3c);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 1rem;
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

    .btn-home, .btn-refresh {
      width: 200px;
    }
  }
</style>
@endpush

@section('content')
  <div class="error-hero">
    <div class="error-content">
      <div class="error-number">500</div>
      <h1 class="error-title">Oops! Server Error</h1>
      <p class="error-message">
        Something went wrong on our end! Our team has been notified and is working to fix the issue.
        Please try again in a few moments.
      </p>
      <div class="error-actions">
        <a href="{{ url('/') }}" class="btn-home">
          <i class="bi  bi-home me-2"></i>Go Home
        </a>
        <a href="javascript:location.reload()" class="btn-refresh">
          <i class="bi  bi-redo me-2"></i>Try Again
        </a>
      </div>
    </div>
  </div>

  <section class="error-features">
    <div class="container">
      <div class="row text-center mb-5">
        <div class="col-12">
          <h2 class="display-5 fw-bold text-dark mb-3">Need Help?</h2>
          <p class="lead text-muted">Our support team is here to help you</p>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-4 col-md-6">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="bi  bi-phone"></i>
            </div>
            <h3 class="feature-title">Call Us</h3>
            <p class="feature-text">
              Speak directly with our support team for immediate assistance with your booking.
            </p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="bi  bi-envelope"></i>
            </div>
            <h3 class="feature-title">Email Support</h3>
            <p class="feature-text">
              Send us a detailed message and we'll get back to you within 24 hours.
            </p>
          </div>
        </div>

        <div class="col-lg-4 col-md-6">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="bi  bi-comments"></i>
            </div>
            <h3 class="feature-title">Live Chat</h3>
            <p class="feature-text">
              Chat with our support team in real-time for quick answers to your questions.
            </p>
          </div>
        </div>
      </div>

      <div class="contact-info">
        <h3 class="text-center mb-4">Contact Information</h3>
        <div class="row">
          <div class="col-md-4">
            <div class="contact-item">
              <div class="contact-icon">
                <i class="bi  bi-phone"></i>
              </div>
              <div>
                <strong>Phone</strong><br>
                <span>(+62) 81 2345 1234</span>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="contact-item">
              <div class="contact-icon">
                <i class="bi  bi-envelope"></i>
              </div>
              <div>
                <strong>Email</strong><br>
                <span>support@adventours.com</span>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="contact-item">
              <div class="contact-icon">
                <i class="bi  bi-clock"></i>
              </div>
              <div>
                <strong>Hours</strong><br>
                <span>9 AM - 10 PM Daily</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-5 text-center">
        <div class="col-12">
          <a href="{{ url('/') }}" class="btn btn-lg jatio-bg-color text-white px-5 py-3">
            <i class="bi  bi-home me-2"></i>Return to Homepage
          </a>
        </div>
      </div>
    </div>
  </section>
@endsection
