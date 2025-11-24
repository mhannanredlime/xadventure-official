@extends('layouts.frontend')

@section('title', 'Archery')

@push('styles')
  <link rel="stylesheet" href="{{ asset('frontEnd/css/archery.css') }}">
@endpush

@section('content')
  <main class="main-section" style="margin-top: 5%;">
    <div class="container">
      <h1 class="main-heading">Regular/Archery</h1>

      <div class="package-card">
        <div class="package-details">
          <img src="images/archery.svg" alt="Archery">
          <div class="package-info">
            <h5>Archery And Target Games</h5>
            <p>Base price</p>
            <p class="price">TK 3500</p>
          </div>
        </div>

        <div class="package-counter d-flex justify-content-center align-items-center">
          <div>
            <button id="minus-btn">-</button>
            <span class="count" id="package-count">3 Package</span>
            <button id="plus-btn">+</button>
          </div>
        </div>

        <div class="total-section-container">
          <div class="total-section">
            <p class="total-label">TOTAL</p>
            <p class="total-price" id="total-price">Tk 7000</p>
          </div>
          <div class="close-section">
            <button class="close-btn">&times;</button>
          </div>
        </div>
      </div>

      <div class="light-card mt-5">
        <h2 class="section-title">Choose Date</h2>
        <div class="calendar-container">
          <div class="row calendar-months">
            <div class="col-md-6">
              <div class="calendar-header">
                <button class="calendar-nav"><i class="fas fa-chevron-left"></i></button>
                <h5>February 2022</h5>
              </div>
              <div class="calendar-grid">
                <div class="day-name">Su</div>
                <div class="day-name">Mo</div>
                <div class="day-name">Tu</div>
                <div class="day-name">We</div>
                <div class="day-name">Th</div>
                <div class="day-name">Fr</div>
                <div class="day-name">Sa</div>
                <div class="day-number empty"></div>
                <div class="day-number empty"></div>
                <div class="day-number active">1</div>
                <div class="day-number">2</div>
                <div class="day-number">3</div>
                <div class="day-number">4</div>
                <div class="day-number">5</div>
                <div class="day-number">6</div>
                <div class="day-number">7</div>
                <div class="day-number">8</div>
                <div class="day-number">9</div>
                <div class="day-number">10</div>
                <div class="day-number">11</div>
                <div class="day-number">12</div>
                <div class="day-number">13</div>
                <div class="day-number">14</div>
                <div class="day-number">15</div>
                <div class="day-number">16</div>
                <div class="day-number">17</div>
                <div class="day-number">18</div>
                <div class="day-number">19</div>
                <div class="day-number">20</div>
                <div class="day-number">21</div>
                <div class="day-number">22</div>
                <div class="day-number">23</div>
                <div class="day-number">24</div>
                <div class="day-number">25</div>
                <div class="day-number">26</div>
                <div class="day-number">27</div>
                <div class="day-number">28</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="calendar-header justify-content-end">
                <h5>March 2022</h5>
                <button class="calendar-nav"><i class="fas fa-chevron-right"></i></button>
              </div>
              <div class="calendar-grid">
                <div class="day-name">Su</div>
                <div class="day-name">Mo</div>
                <div class="day-name">Tu</div>
                <div class="day-name">We</div>
                <div class="day-name">Th</div>
                <div class="day-name">Fr</div>
                <div class="day-name">Sa</div>
                <div class="day-number empty"></div>
                <div class="day-number empty"></div>
                <div class="day-number">1</div>
                <div class="day-number">2</div>
                <div class="day-number">3</div>
                <div class="day-number">4</div>
                <div class="day-number">5</div>
                <div class="day-number">6</div>
                <div class="day-number">7</div>
                <div class="day-number">8</div>
                <div class="day-number">9</div>
                <div class="day-number">10</div>
                <div class="day-number">11</div>
                <div class="day-number">12</div>
                <div class="day-number">13</div>
                <div class="day-number">14</div>
                <div class="day-number">15</div>
                <div class="day-number">16</div>
                <div class="day-number">17</div>
                <div class="day-number">18</div>
                <div class="day-number">19</div>
                <div class="day-number">20</div>
                <div class="day-number">21</div>
                <div class="day-number">22</div>
                <div class="day-number">23</div>
                <div class="day-number">24</div>
                <div class="day-number">25</div>
                <div class="day-number">26</div>
                <div class="day-number">27</div>
                <div class="day-number">28</div>
                <div class="day-number">29</div>
                <div class="day-number">30</div>
                <div class="day-number">31</div>
              </div>
            </div>
          </div>
        </div>
        <h2 class="section-title mt-5">Choose Preferred Slot</h2>
        <div class="row">
          <div class="col-md-3"><button class="slot-button active">Morning</button></div>
          <div class="col-md-3"><button class="slot-button">Evening</button></div>
        </div>
      </div>
    </div>
  </main>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const minusBtn = document.getElementById('minus-btn');
    const plusBtn = document.getElementById('plus-btn');
    const packageCountEl = document.getElementById('package-count');
    const totalPriceEl = document.getElementById('total-price');
    const basePrice = 3500;
    let packageCount = 3;
    function updateDisplay() {
      packageCountEl.textContent = `${packageCount} Package${packageCount === 1 ? '' : 's'}`;
      totalPriceEl.textContent = `TK ${basePrice * packageCount}`;
    }
    plusBtn.addEventListener('click', () => { packageCount++; updateDisplay(); });
    minusBtn.addEventListener('click', () => { if (packageCount > 1) { packageCount--; updateDisplay(); } });
    const days = document.querySelectorAll('.day-number:not(.empty)');
    days.forEach(day => {
      day.addEventListener('click', function () {
        days.forEach(d => d.classList.remove('active'));
        this.classList.add('active');
      });
    });
    const slotButtons = document.querySelectorAll('.slot-button');
    slotButtons.forEach(button => {
      button.addEventListener('click', function () {
        slotButtons.forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
      });
    });
  });
</script>
@endpush


