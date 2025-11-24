/**
 * Calendar Manager - Handles all calendar functionality
 */
class CalendarManager {
    constructor() {
        this.selectedDate = null;
        this.selectedPackage = null;
        this.selectedVariant = null;
        this.currentMonth = new Date();
        this.calendarData = {};
        this.packages = [];
        this.currentPriceOverride = null; // Store current price override data
        this.isLoading = false;
        this.loadingStates = {
            packageData: false,
            availability: false,
            pricing: false,
            calendar: false,
            formSubmission: false
        };
        // Range selection state
        this.rangeSelectionMode = true; // Enable by default
        this.rangeStartDate = null;
        this.rangeEndDate = null;
        this.appliedRangePrices = null; // Store applied range prices for UI display
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadInitialData();
    }

    // Loading state management
    setLoadingState(state, isLoading) {
        this.loadingStates[state] = isLoading;
        this.updateLoadingUI(state, isLoading);
    }

    updateLoadingUI(state, isLoading) {
        const loadingElements = {
            packageData: ['#selectPackage', '.calendar-container'],
            availability: ['#availabilityForm', '.availability-section'],
            pricing: ['.pricing-section-container'],
            calendar: ['#calendarGrid', '#calendarGridNext'],
            formSubmission: ['#availabilityForm button[type="submit"]']
        };

        const elements = loadingElements[state] || [];
        elements.forEach(selector => {
            const element = document.querySelector(selector);
            if (element) {
                if (isLoading) {
                    element.classList.add('loading');
                    if (element.tagName === 'BUTTON') {
                        element.disabled = true;
                        element.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
                    }
                } else {
                    element.classList.remove('loading');
                    if (element.tagName === 'BUTTON') {
                        element.disabled = false;
                        element.innerHTML = 'Update Availability';
                    }
                }
            }
        });

        // Show/hide global loading overlay for major operations
        if (state === 'packageData' || state === 'calendar') {
            this.toggleGlobalLoading(isLoading);
        }
    }

    toggleGlobalLoading(show) {
        // Loading overlay disabled - no longer showing global loading modal
        return;
    }

    showLoadingMessage(message = 'Loading...') {
        // Removed unnecessary loading toast notifications
        console.log('Loading:', message);
    }

    showSuccessMessage(message) {
        // Removed unnecessary success toast notifications for routine operations
        console.log('Success:', message);
    }

    showErrorMessage(message) {
        console.log('showErrorMessage called with:', message);
        this.showToast(message, 'error');
        console.log('showToast called');
    }

    showToast(message, type = 'info') {
        console.log('showToast called with message:', message, 'type:', type);
        
        // Use the global toast notification system
        if (typeof toastNotifications !== 'undefined') {
            console.log('Using global toastNotifications');
            return toastNotifications.show(message, type);
        } else if (typeof showToast !== 'undefined') {
            console.log('Using global showToast function');
            return showToast(message, type);
        } else {
            console.log('Toast system not available, falling back to alert');
            alert(`${type.toUpperCase()}: ${message}`);
        }
    }

    bindEvents() {
        // Package selection
        const packageSelect = document.getElementById('selectPackage');
        if (packageSelect) {
            packageSelect.addEventListener('change', (e) => {
                this.handlePackageChange(e.target.value);
            });
        }

        // Month navigation
        const prevMonth = document.querySelector('.fa-chevron-left');
        const nextMonth = document.querySelector('.fa-chevron-right');
        
        if (prevMonth) {
            prevMonth.addEventListener('click', () => {
                this.previousMonth();
            });
        }
        
        if (nextMonth) {
            nextMonth.addEventListener('click', () => {
                this.nextMonth();
            });
        }

        // Form submission handler removed since we use on-change updating methods

        // Day off toggle
        const dayOffSwitch = document.getElementById('dayOffSwitch');
        if (dayOffSwitch) {
            dayOffSwitch.addEventListener('change', (e) => {
                this.handleDayOffToggle(e.target.checked);
            });
        }

        // Special price toggle
        const specialPriceSwitch = document.getElementById('specialPriceSwitch');
        if (specialPriceSwitch) {
            specialPriceSwitch.addEventListener('change', (e) => {
                this.handleSpecialPriceToggle(e.target.checked);
            });
        }

        // Price option change (Premium/Discounted)
        const priceOptions = document.querySelectorAll('input[name="priceOption"]');
        priceOptions.forEach(option => {
            option.addEventListener('change', (e) => {
                this.handlePriceOptionChange(e.target.value);
            });
        });

        // Available vehicles input
        const availableVehicles = document.getElementById('availableVehicles');
        if (availableVehicles) {
            // Update display immediately on input
            availableVehicles.addEventListener('input', () => {
                this.updateAvailableSlots();
                // Mark as modified
                availableVehicles.classList.add('modified');
                availableVehicles.style.borderColor = '#ffc107';
            });
            
            // Save changes when user finishes editing (on blur)
            availableVehicles.addEventListener('blur', () => {
                this.saveAvailableVehicles();
            });
            
            // Also save on Enter key
            availableVehicles.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    availableVehicles.blur(); // Trigger blur event to save
                }
            });
        }

        // Vehicle type selection removed - vehicle types are determined by package selection
    }

    async loadInitialData() {
        try {
            this.setLoadingState('packageData', true);
            // Initializing calendar - no toast needed

            // Ensure all prices have original_amount set
            await this.ensureOriginalAmounts();

            const packageSelect = document.getElementById('selectPackage');
            if (packageSelect && packageSelect.value) {
                // Load all packages first
                await this.loadAllPackages();
                
                // Set initial package state
                this.selectedPackage = this.packages.find(p => p.id == packageSelect.value);
                console.log('Initial package selection:', {
                    selectedPackageId: packageSelect.value,
                    selectedPackage: this.selectedPackage,
                    allPackages: this.packages
                });
                if (this.selectedPackage) {
                    this.handleInitialPackageState();
                }
                
                await this.loadPackageData(packageSelect.value);

                // Ensure a default variant is selected
                if (!this.selectedVariant && this.selectedPackage && this.selectedPackage.variants && this.selectedPackage.variants.length > 0) {
                    this.selectedVariant = this.selectedPackage.variants[0].id;
                }

                // Select today's date by default and load all related panels
                // Disabled auto-selection on page load - user should manually select dates
                // const today = this.getTodayString();
                // this.selectedDate = today;
                // // Generate calendar selection UI before selecting
                // this.updateCalendarSelection(today);
                // // Trigger full panel load
                // await this.loadAvailabilityForDate(today);
                
                // Initialize range selection UI state
                this.updateRangeSelectionUI();
                
                // Check if there are any existing price overrides and restore range selection if needed
                // Disabled automatic range restoration on page load
                // Delay this to allow initial data loading to complete
                // setTimeout(() => {
                //     this.restoreRangeSelectionFromData();
                // }, 1000);
                
                // Show initial message for range selection mode
                this.showSuccessMessage('Range selection mode is enabled by default. Click start date, then end date to select a range.');
                
                // Calendar loaded successfully - no toast needed
            } else {
                // Generate empty calendar if no package selected
                this.generateCalendar();
                // No package selected - no toast needed
            }
        } catch (error) {
            console.error('Error loading initial data:', error);
            this.showErrorMessage(`Failed to initialize calendar: ${error.message}`);
            // Still generate calendar even if package data fails
            this.generateCalendar();
        } finally {
            this.setLoadingState('packageData', false);
        }
    }

    async loadAllPackages() {
        try {
            const response = await fetch('/admin/calendar/package/1/data'); // Load first package to get all packages
            if (response.ok) {
                const data = await response.json();
                // Store all packages from the dropdown
                const packageSelect = document.getElementById('selectPackage');
                if (packageSelect) {
                                    this.packages = Array.from(packageSelect.options).map(option => ({
                    id: parseInt(option.value),
                    name: option.textContent,
                    type: option.dataset.type || 'regular'
                }));
                
                // Debug logging for packages
                console.log('Loaded packages:', this.packages);
                }
            }
        } catch (error) {
            // Error loading all packages
        }
    }

    async loadPackageData(packageId) {
        try {
            this.setLoadingState('packageData', true);
            // Loading package data - no toast needed

            // Ensure backend receives the month currently being viewed so
            // calendar statuses match the UI month(s)
            const monthParam = `${this.currentMonth.getFullYear()}-${String(this.currentMonth.getMonth() + 1).padStart(2, '0')}`;
            const url = `/admin/calendar/package/${packageId}/data?month=${monthParam}`;
            
            const response = await fetch(url);
            
            if (!response.ok) {
                let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
                try {
                    const errorData = await response.json();
                    errorMessage = errorData.error || errorData.message || errorMessage;
                } catch (e) {
                    // If JSON parsing fails, use the text response
                    const errorText = await response.text();
                    errorMessage = errorText || errorMessage;
                }
                throw new Error(errorMessage);
            }
            
            const data = await response.json();
            
            if (!data.package) {
                throw new Error('Invalid package data received from server');
            }
            
            this.selectedPackage = data.package;
            this.calendarData = data.calendar_data || {};
            
            // Set the selected variant to the first variant of the package
            if (this.selectedPackage && this.selectedPackage.variants && this.selectedPackage.variants.length > 0) {
                this.selectedVariant = this.selectedPackage.variants[0].id;
                console.log('Set selected variant to:', this.selectedVariant);
            }
            
            // Handle package state after loading data
            this.handleInitialPackageState();
            
            this.generateCalendar();
            await this.updatePricingCards();
            
            // Ensure capacity section is properly hidden for regular packages
            if (this.selectedPackage && this.selectedPackage.type === 'regular') {
                const capacitySection = document.querySelector('.card.mb-3.p-3.bg-light')?.closest('.card');
                if (capacitySection) {
                    capacitySection.style.display = 'none';
                }
            }
            
            // Package data loaded successfully - no toast needed
        } catch (error) {
            console.error('Error loading package data:', error);
            this.showErrorMessage(`Failed to load package data: ${error.message}`);
            // Still generate calendar even if package data fails
            this.generateCalendar();
        } finally {
            this.setLoadingState('packageData', false);
        }
    }

    generateCalendar() {
        const calendarGrid = document.getElementById('calendarGrid');
        const calendarGridNext = document.getElementById('calendarGridNext');
        
        if (!calendarGrid) return;

        const year = this.currentMonth.getFullYear();
        const month = this.currentMonth.getMonth() + 1;
        
        // Generate first month
        this.generateMonthCalendar(calendarGrid, year, month);
        
        // Generate second month
        if (calendarGridNext) {
            const nextMonth = month === 12 ? 1 : month + 1;
            const nextYear = month === 12 ? year + 1 : year;
            this.generateMonthCalendar(calendarGridNext, nextYear, nextMonth);
        }
        
        this.updateMonthDisplay();
    }
    
    generateMonthCalendar(container, year, month) {
        const firstDay = new Date(year, month - 1, 1);
        const lastDay = new Date(year, month, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay());
        const targetMonthIndex = month - 1; // 0-indexed target month to compare against
        
        let calendarHTML = '';
        
        // Add day headers
        const dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
        dayNames.forEach(day => {
            calendarHTML += `<span class="day-name">${day}</span>`;
        });
        
        // Generate calendar grid
        for (let i = 0; i < 42; i++) {
            const currentDate = new Date(startDate);
            currentDate.setDate(startDate.getDate() + i);
            
            // Create date string manually to avoid timezone issues
            const yearNum = currentDate.getFullYear();
            const monthStr = String(currentDate.getMonth() + 1).padStart(2, '0');
            const day = String(currentDate.getDate()).padStart(2, '0');
            const dateString = `${yearNum}-${monthStr}-${day}`;
            
            // Compare against the intended month, not the local loop variable
            const isCurrentMonth = currentDate.getMonth() === targetMonthIndex;
            const isToday = this.isToday(currentDate);
            const status = this.getDateStatus(dateString);
            
            const classes = this.getDateClasses(isCurrentMonth, isToday, status, dateString);
            
            // Add inline styles to match design exactly
            let inlineStyle = '';
            if (status === 'today') {
                inlineStyle = 'style="background-color: #007bff !important; color: white !important;"';
            } else if (status === 'booked') {
                inlineStyle = 'style="background-color: transparent !important; color: #ff6600 !important; font-weight: bold !important;"';
            } else if (status === 'premium') {
                inlineStyle = 'style="background-color: transparent !important; color: #28a745 !important; font-weight: bold !important;"';
            } else if (status === 'discounted') {
                inlineStyle = 'style="background-color: transparent !important; color: #6f42c1 !important; font-weight: bold !important;"';
            } else if (status === 'day-off') {
                inlineStyle = 'style="background-color: transparent !important; color: #6c757d !important; font-weight: bold !important;"';
            } else if (status === 'selected') {
                inlineStyle = 'style="background-color: #007bff !important; color: white !important;"';
            }
            
            calendarHTML += `
                <span class="date ${classes}" data-date="${dateString}" onclick="calendarManager.selectDate('${dateString}')" ${inlineStyle}>
                    ${currentDate.getDate()}
                    ${this.getDateIndicator(status)}
                </span>
            `;
        }
        
        container.innerHTML = calendarHTML;
    }

    async selectDate(date) {
        if (this.rangeSelectionMode) {
            this.handleRangeSelection(date);
        } else {
            this.selectedDate = date;
            this.updateDateDisplay(date);
            this.loadAvailabilityForDate(date);
            this.updateCalendarSelection(date);
            await this.updatePricingCards(); // Update pricing cards based on selected date
        }
    }

    // Range Selection Methods
    async handleRangeSelection(date) {
        if (!this.rangeStartDate) {
            // First click - set start date
            this.rangeStartDate = date;
            this.selectedDate = date; // Set selected date for single date operations
            this.updateDateDisplay(date);
            this.loadAvailabilityForDate(date); // Load availability and price data
            this.updateRangeDisplay();
            this.updateCalendarSelection(date);
            this.showSuccessMessage('Start date selected. Click end date to complete range.');
        } else if (!this.rangeEndDate) {
            // Second click - set end date
            this.rangeEndDate = date;
            
            // Ensure start date is before end date
            if (new Date(this.rangeStartDate) > new Date(this.rangeEndDate)) {
                // Swap dates if end is before start
                const temp = this.rangeStartDate;
                this.rangeStartDate = this.rangeEndDate;
                this.rangeEndDate = temp;
            }
            
            this.selectedDate = date; // Set selected date for single date operations
            this.updateDateDisplay(date);
            this.loadAvailabilityForDate(date); // Load availability and price data
            this.updateRangeDisplay();
            this.updateCalendarSelection(date);
            await this.updatePricingCards(); // Update pricing cards to show both weekday and weekend
            this.updateRangeSelectionUI(); // Show the Apply to Range button
            this.showRangeSelectionComplete();
        } else {
            // Reset and start new selection
            this.clearRangeSelection();
            this.rangeStartDate = date;
            this.selectedDate = date; // Set selected date for single date operations
            this.updateDateDisplay(date);
            this.loadAvailabilityForDate(date); // Load availability and price data
            this.updateRangeDisplay();
            this.updateCalendarSelection(date);
            this.showSuccessMessage('New range started. Click end date to complete range.');
        }
    }

    enableRangeSelection() {
        this.rangeSelectionMode = true;
        this.clearRangeSelection();
        this.updateRangeSelectionUI();
        this.showSuccessMessage('Range selection mode enabled. Click start date, then end date.');
    }

    async exitRangeSelection() {
        this.rangeSelectionMode = false;
        await this.clearRangeSelection();
        this.updateRangeSelectionUI();
        this.showSuccessMessage('Range selection mode disabled.');
    }

    async clearRangeSelection() {
        this.rangeStartDate = null;
        this.rangeEndDate = null;
        this.updateRangeDisplay();
        this.updateCalendarSelection();
        this.updateRangeSelectionUI();
        await this.updatePricingCards(); // Refresh pricing cards when range is cleared
    }

    getSelectedDates() {
        if (!this.rangeStartDate || !this.rangeEndDate) {
            return [];
        }

        const dates = [];
        const startDate = new Date(this.rangeStartDate);
        const endDate = new Date(this.rangeEndDate);
        
        for (let date = new Date(startDate); date <= endDate; date.setDate(date.getDate() + 1)) {
            const dateString = date.toISOString().split('T')[0];
            dates.push(dateString);
        }
        
        return dates;
    }

    isDateInRange(date) {
        if (!this.rangeStartDate || !this.rangeEndDate) {
            return false;
        }
        
        const checkDate = new Date(date);
        const startDate = new Date(this.rangeStartDate);
        const endDate = new Date(this.rangeEndDate);
        
        return checkDate >= startDate && checkDate <= endDate;
    }

    updateRangeDisplay() {
        const rangeInfo = document.getElementById('rangeSelectionInfo');
        if (rangeInfo) {
            if (this.rangeStartDate && this.rangeEndDate) {
                const startFormatted = this.formatDate(this.rangeStartDate);
                const endFormatted = this.formatDate(this.rangeEndDate);
                rangeInfo.textContent = `Selected Range: ${startFormatted} - ${endFormatted}`;
                rangeInfo.style.display = 'block';
            } else if (this.rangeStartDate) {
                const startFormatted = this.formatDate(this.rangeStartDate);
                rangeInfo.textContent = `Start Date: ${startFormatted} (Click end date)`;
                rangeInfo.style.display = 'block';
            } else {
                rangeInfo.style.display = 'none';
            }
        }
    }

    updateRangeSelectionUI() {
        const rangeToggle = document.getElementById('rangeSelectionToggle');
        const applyButton = document.getElementById('applyToRangeButton');
        const rangeInfo = document.getElementById('rangeSelectionInfo');
        
        if (rangeToggle) {
            if (this.rangeSelectionMode) {
                rangeToggle.textContent = 'Exit Range Selection';
                rangeToggle.className = 'btn btn-warning btn-sm';
                rangeToggle.onclick = () => this.exitRangeSelection();
            } else {
                rangeToggle.textContent = 'Select Date Range';
                rangeToggle.className = 'btn btn-outline-primary btn-sm';
                rangeToggle.onclick = () => this.enableRangeSelection();
            }
        }
        
        if (applyButton) {
            applyButton.style.display = (this.rangeStartDate && this.rangeEndDate) ? 'block' : 'none';
        }
    }

    showRangeSelectionComplete() {
        const dates = this.getSelectedDates();
        const hasWeekdays = dates.some(date => !this.isWeekendDate(date));
        const hasWeekends = dates.some(date => this.isWeekendDate(date));
        
        let message = `Range selected: ${dates.length} dates from ${this.formatDate(this.rangeStartDate)} to ${this.formatDate(this.rangeEndDate)}. `;
        
        if (hasWeekdays && hasWeekends) {
            message += "Both weekday and weekend pricing sections are now visible. Enable special pricing and click 'Apply to Range' to update all dates.";
        } else if (hasWeekdays) {
            message += "Only weekday pricing section is visible (range contains only weekdays). Enable special pricing and click 'Apply to Range' to update all dates.";
        } else {
            message += "Only weekend pricing section is visible (range contains only weekends). Enable special pricing and click 'Apply to Range' to update all dates.";
        }
        
        this.showSuccessMessage(message);
    }

    async restoreRangeSelectionFromData() {
        try {
            console.log('Attempting to restore range selection from existing data...');
            
            // Look for consecutive dates with price overrides in the existing calendar data cache
            const datesWithOverrides = [];
            
            // Get all dates that have price overrides from the calendar data cache
            Object.keys(this.calendarData).forEach(dateString => {
                const data = this.calendarData[dateString];
                if (data && data.price_override && data.price_override.price_tag) {
                    datesWithOverrides.push(dateString);
                }
            });
            
            console.log('Dates with price overrides found in cache:', datesWithOverrides);
            
            if (datesWithOverrides.length > 1) {
                // Sort dates to find consecutive ranges
                datesWithOverrides.sort();
                
                // Find the largest consecutive range
                let longestRange = [];
                let currentRange = [datesWithOverrides[0]];
                
                for (let i = 1; i < datesWithOverrides.length; i++) {
                    const prevDate = new Date(datesWithOverrides[i - 1]);
                    const currentDate = new Date(datesWithOverrides[i]);
                    const dayDiff = (currentDate - prevDate) / (1000 * 60 * 60 * 24);
                    
                    if (dayDiff === 1) {
                        // Consecutive day
                        currentRange.push(datesWithOverrides[i]);
                    } else {
                        // Not consecutive, check if this range is longer
                        if (currentRange.length > longestRange.length) {
                            longestRange = [...currentRange];
                        }
                        currentRange = [datesWithOverrides[i]];
                    }
                }
                
                // Check the last range
                if (currentRange.length > longestRange.length) {
                    longestRange = [...currentRange];
                }
                
                console.log('Longest consecutive range found:', longestRange);
                
                // If we found a range of 2 or more consecutive dates, restore it
                if (longestRange.length >= 2) {
                    this.rangeStartDate = longestRange[0];
                    this.rangeEndDate = longestRange[longestRange.length - 1];
                    this.selectedDate = this.rangeEndDate; // Set selected date to end date
                    
                    // Update UI to show the restored range
                    this.updateRangeDisplay();
                    this.updateCalendarSelection(this.rangeEndDate);
                    await this.updatePricingCards();
                    
                    console.log('Restored range selection:', {
                        start: this.rangeStartDate,
                        end: this.rangeEndDate,
                        count: longestRange.length
                    });
                    
                    this.showSuccessMessage(`Restored range selection: ${longestRange.length} dates from ${this.rangeStartDate} to ${this.rangeEndDate}`);
                } else {
                    console.log('No consecutive range found with 2+ dates');
                }
            } else {
                console.log('Not enough dates with price overrides to restore range');
            }
        } catch (error) {
            console.error('Error restoring range selection:', error);
        }
    }

    collectPriceAmountsFromUI() {
        const priceAmounts = {};
        
        // Get all pricing cards
        const pricingCards = document.querySelectorAll('.pricing-card');
        
        pricingCards.forEach(card => {
            const variantId = card.getAttribute('data-variant-id');
            const priceDisplay = card.querySelector('.price-display');
            
            if (variantId && priceDisplay) {
                // Extract the price amount from the display
                const priceText = priceDisplay.textContent.trim();
                const priceAmount = parseFloat(priceText.replace(/[^\d.]/g, ''));
                
                if (!isNaN(priceAmount)) {
                    priceAmounts[variantId] = priceAmount;
                    console.log(`Collected price for variant ${variantId}: ${priceAmount}`);
                }
            }
        });
        
        console.log('Collected price amounts:', priceAmounts);
        return priceAmounts;
    }

    collectRangePricingFromUI() {
        const weekdayPricing = {};
        const weekendPricing = {};
        
        // Collect weekday pricing
        const weekdayCards = document.querySelectorAll('#weekdayPricingCards .pricing-card');
        weekdayCards.forEach(card => {
            const variantId = card.getAttribute('data-variant-id');
            const priceDisplay = card.querySelector('.price-display');
            
            if (variantId && priceDisplay) {
                const priceText = priceDisplay.textContent.trim();
                const priceAmount = parseFloat(priceText.replace(/[^\d.]/g, ''));
                
                if (!isNaN(priceAmount)) {
                    weekdayPricing[variantId] = priceAmount;
                    console.log(`Collected weekday price for variant ${variantId}: ${priceAmount}`);
                }
            }
        });
        
        // Collect weekend pricing
        const weekendCards = document.querySelectorAll('#weekendPricingCards .pricing-card');
        weekendCards.forEach(card => {
            const variantId = card.getAttribute('data-variant-id');
            const priceDisplay = card.querySelector('.price-display');
            
            if (variantId && priceDisplay) {
                const priceText = priceDisplay.textContent.trim();
                const priceAmount = parseFloat(priceText.replace(/[^\d.]/g, ''));
                
                if (!isNaN(priceAmount)) {
                    weekendPricing[variantId] = priceAmount;
                    console.log(`Collected weekend price for variant ${variantId}: ${priceAmount}`);
                }
            }
        });
        
        console.log('Collected range pricing:', { weekday: weekdayPricing, weekend: weekendPricing });
        return {
            weekday: weekdayPricing,
            weekend: weekendPricing
        };
    }

    async applyPricingToRange() {
        if (!this.rangeStartDate || !this.rangeEndDate) {
            this.showErrorMessage('Please select a date range first');
            return;
        }

        if (!this.selectedVariant) {
            this.showErrorMessage('No package variant selected');
            return;
        }

        const specialPriceEnabled = document.getElementById('specialPriceSwitch')?.checked || false;
        const priceTag = document.querySelector('input[name="priceOption"]:checked')?.value || 'premium';

        if (!specialPriceEnabled) {
            this.showErrorMessage('Please enable special pricing first');
            return;
        }

        const dates = this.getSelectedDates();
        if (dates.length === 0) {
            this.showErrorMessage('No dates in selected range');
            return;
        }

        // Validate date range (max 90 days)
        if (dates.length > 90) {
            this.showErrorMessage('Date range cannot exceed 90 days');
            return;
        }

        try {
            this.setLoadingState('formSubmission', true);
            this.showLoadingMessage(`Applying pricing to ${dates.length} dates...`);

            // Show visual feedback on the range
            this.highlightRangeSelection();
            const rangeElements = document.querySelectorAll('.date.range-start, .date.range-end, .date.range-middle');
            rangeElements.forEach(el => el.style.opacity = '0.6');

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                throw new Error('CSRF token not found. Please refresh the page.');
            }

            // Collect weekday and weekend pricing separately for range
            const rangePricing = this.collectRangePricingFromUI();
            
            const formData = {
                package_variant_id: this.selectedVariant,
                start_date: this.rangeStartDate,
                end_date: this.rangeEndDate,
                is_day_off: false, // Don't set day off for range
                capacity_total: 0, // Don't update capacity for range pricing
                special_price_enabled: specialPriceEnabled,
                price_tag: priceTag,
                weekday_pricing: rangePricing.weekday, // Send weekday pricing
                weekend_pricing: rangePricing.weekend  // Send weekend pricing
            };

            console.log('Sending bulk update request:', formData);
            console.log('Date range:', { start: this.rangeStartDate, end: this.rangeEndDate, count: dates.length });
            
            const response = await fetch('/admin/calendar/availability/bulk', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData),
                credentials: 'same-origin'
            });

            if (!response.ok) {
                let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
                try {
                    const errorData = await response.json();
                    errorMessage = errorData.error || errorData.message || errorMessage;
                } catch (e) {
                    const errorText = await response.text();
                    errorMessage = errorText || errorMessage;
                }
                throw new Error(errorMessage);
            }

            const result = await response.json();
            console.log('Bulk update response:', result);
            
            if (result.success) {
                this.showSuccessMessage(`Special pricing applied to ${dates.length} dates successfully`);
                
                // Store the applied range prices so they can be displayed in the UI
                this.appliedRangePrices = rangePricing;
                
                // Keep range selection active for further changes
                // Don't clear range selection - let user continue making changes
                
                // Reload calendar data to show updated pricing
                if (this.selectedPackage) {
                    await this.loadPackageData(this.selectedPackage.id);
                }
                
                // Refresh calendar to show updated colors
                this.generateCalendar();
                if (this.selectedDate) {
                    this.updateCalendarSelection(this.selectedDate);
                }
                
                // Update pricing cards to reflect changes
                await this.updatePricingCards();
            } else {
                throw new Error(result.message || 'Update failed');
            }
        } catch (error) {
            console.error('Error applying pricing to range:', error);
            this.showErrorMessage(`Failed to apply pricing to range: ${error.message}`);
        } finally {
            // Restore opacity
            const rangeElements = document.querySelectorAll('.date.range-start, .date.range-end, .date.range-middle');
            rangeElements.forEach(el => el.style.opacity = '1');
            
            this.setLoadingState('formSubmission', false);
        }
    }

    async loadAvailabilityForDate(date) {
        if (!this.selectedVariant) {
            // Get first variant from selected package
            if (this.selectedPackage && this.selectedPackage.variants && this.selectedPackage.variants.length > 0) {
                this.selectedVariant = this.selectedPackage.variants[0].id;
            } else {
                this.showErrorMessage('No package variant selected');
                return;
            }
        }

        try {
            this.setLoadingState('availability', true);
            // Loading availability data - no toast needed

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                throw new Error('CSRF token not found. Please refresh the page.');
            }
            
            const response = await fetch('/admin/calendar/availability', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    package_variant_id: this.selectedVariant,
                    date: date
                }),
                credentials: 'same-origin'
            });

            if (!response.ok) {
                let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
                try {
                    const errorData = await response.json();
                    errorMessage = errorData.error || errorData.message || errorMessage;
                } catch (e) {
                    const errorText = await response.text();
                    errorMessage = errorText || errorMessage;
                }
                throw new Error(errorMessage);
            }

            const data = await response.json();
            
            if (!data) {
                throw new Error('No data received from server');
            }
            
            await this.updateActionsPanel(data);
            // Availability data loaded successfully - no toast needed
        } catch (error) {
            console.error('Error loading availability:', error);
            this.showErrorMessage(`Failed to load availability data: ${error.message}`);
            
            // Show fallback UI when availability loading fails
            this.showFallbackAvailabilityUI();
        } finally {
            this.setLoadingState('availability', false);
        }
    }

    showFallbackAvailabilityUI() {
        // Show a basic fallback UI when availability loading fails
        const availabilitySection = document.querySelector('.availability-section');
        if (availabilitySection) {
            availabilitySection.innerHTML = `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Unable to load availability data. Please try refreshing the page or contact support if the problem persists.
                </div>
            `;
        }
    }

    async updateActionsPanel(data) {
        // Store current availability data for reference
        this.currentAvailability = data.availability || {};
        
        // Update date display
        const selectedDateElement = document.getElementById('selectedDate');
        if (selectedDateElement) {
            // Use the selected date from the calendar, not from the server response
            // to avoid timezone issues
            const formattedDate = this.formatDate(this.selectedDate);
            selectedDateElement.textContent = formattedDate;
        }
        
        // Update day off toggle
        const dayOffSwitch = document.getElementById('dayOffSwitch');
        if (dayOffSwitch) {
            dayOffSwitch.checked = data.availability?.is_day_off || false;
        }
        
        // Update available vehicles field with current date's availability
        const availableVehicles = document.getElementById('availableVehicles');
        if (availableVehicles && data.availability) {
            // Use the capacity_total from the availability data for this specific date
            const currentCapacity = data.availability.capacity_total || 0;
            availableVehicles.value = currentCapacity;
            
            // Update the display
            this.updateAvailableSlots();
        }
        
        // Fetch dynamic vehicle availability for all package types
        console.log('About to call updateVehicleAvailability for package:', this.selectedPackage?.type);
        await this.updateVehicleAvailability();
        // Populate preset selector and render per-slot controls
        await this.populateSlotPresetSelector(data.slot_preset);
        await this.renderSlotControls();
        
        // Update slot counts based on package type
        const bookedSlots = document.getElementById('bookedSlots');
        const availableSlots = document.getElementById('availableSlots');
        
        if (this.selectedPackage && this.selectedPackage.type === 'regular') {
            // For regular packages, show the actual capacity from the variant
            const capacity = data.vehicle_availability?.total_available || data.vehicle_availability?.total_vehicles || 6;
            console.log('Regular package detected:', {
                packageType: this.selectedPackage.type,
                vehicleAvailability: data.vehicle_availability,
                capacity: capacity
            });
            if (bookedSlots) {
                bookedSlots.textContent = '0'; // Regular packages don't have booked slots in the traditional sense
            }
            if (availableSlots) {
                availableSlots.textContent = capacity; // Show the actual capacity
            }
        } else {
            // For ATV/UTV packages, use the new time slot availability calculation
            if (data.time_slot_availability) {
                if (bookedSlots) {
                    bookedSlots.textContent = data.time_slot_availability.booked_slots || 0;
                }
                if (availableSlots) {
                    availableSlots.textContent = data.time_slot_availability.available_slots || 0;
                }
            } else {
                // Fallback to old calculation if time slot data is not available
                if (bookedSlots) {
                    bookedSlots.textContent = data.availability?.capacity_reserved || 0;
                }
                
                // Update available slots calculation based on vehicle availability
                const total = parseInt(document.getElementById('availableVehicles')?.value) || 0;
                const booked = parseInt(bookedSlots?.textContent) || 0;
                const available = Math.max(0, total - booked);
                
                if (availableSlots) {
                    availableSlots.textContent = available;
                }
            }
        }
        
        // Store current price override data - use all_price_overrides if available, otherwise fallback to single override
        this.currentPriceOverride = data.all_price_overrides ? {
            all_price_overrides: data.all_price_overrides,
            price_override: data.price_override
        } : data.price_override || null;
        
        // Update calendar data cache for this date
        this.updateCalendarDataCache(this.selectedDate, data);
        
        // Update special pricing
        const hasPriceOverride = !!data.price_override;
        const specialPriceSwitch = document.getElementById('specialPriceSwitch');
        if (specialPriceSwitch) {
            specialPriceSwitch.checked = hasPriceOverride;
        }
        
        if (hasPriceOverride) {
            const priceRadio = document.getElementById(data.price_override.price_tag + 'Price');
            if (priceRadio) {
                priceRadio.checked = true;
            }
        } else {
            const discountedPrice = document.getElementById('discountedPrice');
            if (discountedPrice) {
                discountedPrice.checked = true;
            }
        }
        
        this.toggleSpecialPriceFields(hasPriceOverride);
        
        // Update pricing cards based on selected date
        await this.updatePricingCards();
    }

    async populateSlotPresetSelector(activePreset) {
        const select = document.getElementById('slotPresetSelect');
        if (!select) return;
        try {
            const res = await fetch('/admin/calendar/slot-presets');
            if (!res.ok) throw new Error('Failed to load presets');
            const presets = await res.json();
            let html = '<option value="">Default preset</option>';
            presets.forEach(p => {
                const selected = activePreset && activePreset.id === p.id ? 'selected' : '';
                const label = p.is_default ? `${p.name} (Default)` : p.name;
                html += `<option value="${p.id}" ${selected}>${label}</option>`;
            });
            select.innerHTML = html;
            select.onchange = async () => {
                const presetId = select.value || null;
                const payload = {
                    package_variant_id: this.selectedVariant,
                    date: this.selectedDate,
                    slot_preset_id: presetId ? parseInt(presetId) : null,
                };
                const resp = await fetch('/admin/calendar/slot-presets/override', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });
                if (!resp.ok) {
                    this.showErrorMessage('Failed to apply preset');
                    return;
                }
                // Refresh slots based on the new preset
                await this.renderSlotControls();
                // Refresh availability data to update booked/available slots count
                await this.loadAvailabilityForDate(this.selectedDate);
            };
        } catch (e) {
            // Failed to populate preset selector
        }
    }

    async renderSlotControls() {
        if (!this.selectedDate || !this.selectedVariant) return;
        const container = document.getElementById('slotControls');
        if (!container) return;
        try {
            const response = await fetch(`/api/schedule-slots/availability?variant_id=${this.selectedVariant}&date=${this.selectedDate}`);
            if (!response.ok) throw new Error('Failed to load slots');
            const slots = await response.json();
            let html = '<div class="slot-list">';
            slots.forEach(slot => {
                const disabled = slot.available_total <= 0 ? 'disabled' : '';
                const checked = slot.is_open && slot.available_total > 0 ? 'checked' : '';
                html += `
                  <div class="slot-item">
                    <div>
                      <div class="slot-title">${slot.label}</div>
                      <div class="slot-stats">Available: ${slot.available_total} | Booked: ${slot.total_booked}</div>
                    </div>
                    <div class="slot-actions">
                      <input type="number" class="form-control form-control-sm" min="0" value="${slot.available_total}" ${disabled} data-slot-id="${slot.id}" />
                      <div class="form-check form-switch slot-switch">
                        <input class="form-check-input" type="checkbox" ${checked} data-slot-toggle="${slot.id}">
                      </div>
                      <button type="button" class="btn btn-sm btn-primary" data-slot-save="${slot.id}">Save</button>
                    </div>
                  </div>`;
            });
            html += '</div>';
            container.innerHTML = html;
            // Bind save handlers
            container.querySelectorAll('[data-slot-save]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const slotId = btn.getAttribute('data-slot-save');
                    const qtyInput = container.querySelector(`[data-slot-id="${slotId}"]`);
                    const toggle = container.querySelector(`[data-slot-toggle="${slotId}"]`);
                    const payload = {
                        package_variant_id: this.selectedVariant,
                        date: this.selectedDate,
                        schedule_slot_id: parseInt(slotId),
                        is_day_off: toggle ? !toggle.checked : false,
                        capacity_total: qtyInput ? parseInt(qtyInput.value) || 0 : 0
                    };
                    try {
                        const res = await fetch('/admin/calendar/availability/slot/update', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });
                        if (!res.ok) throw new Error('Save failed');
                        const out = await res.json();
                        if (out.success) this.showSuccessMessage('Slot saved'); else this.showErrorMessage('Save failed');
                        // Refresh panel
                        await this.loadAvailabilityForDate(this.selectedDate);
                    } catch (e) {
                        this.showErrorMessage('Save failed');
                    }
                });
            });
        } catch (e) {
            // renderSlotControls error
        }
    }

    async updateVehicleAvailability() {
        if (!this.selectedDate || !this.selectedVariant) {
            return;
        }

        try {
            const params = new URLSearchParams({
                package_variant_id: this.selectedVariant,
                date: this.selectedDate
            });

            const url = `/admin/calendar/vehicle-availability?${params}`;

            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            // Debug logging to see what data we're receiving
            console.log('Vehicle availability data received:', {
                total_available: data.total_available,
                total_vehicles: data.total_vehicles,
                booked_vehicles: data.booked_vehicles,
                vehicle_types: data.vehicle_types,
                full_response: data
            });

            // Update available vehicles field with real vehicle count from vehicle management
            const availableVehicles = document.getElementById('availableVehicles');
            if (availableVehicles) {
                // Update the field value with the actual vehicle count from vehicle management
                const actualVehicleCount = data.total_vehicles || 0;
                console.log('Setting available vehicles to:', actualVehicleCount);
                availableVehicles.value = actualVehicleCount;
                
                // Update tooltip with package vehicle information
                availableVehicles.title = `Package: ${this.selectedPackage?.name || 'Unknown'} | Total: ${data.total_vehicles} vehicles, ${data.booked_vehicles} booked`;
                
                // Add a visual indicator that this is dynamic
                if (!availableVehicles.classList.contains('dynamic-availability')) {
                    availableVehicles.classList.add('dynamic-availability');
                    availableVehicles.style.borderColor = '#28a745';
                    availableVehicles.style.backgroundColor = '#f8fff9';
                }
                
                // Update the display to reflect the new value
                this.updateAvailableSlots();
            }

            // Store vehicle availability data for validation
            this.currentVehicleAvailability = data;
            
            // Update the vehicle type breakdown if available
            if (data.vehicle_types) {
                this.updateVehicleTypeBreakdown(data.vehicle_types);
            }
            
            // Update the capacity label to show the max limit
            this.updateCapacityLabel(data.vehicle_types);
        } catch (error) {
            console.error('Error updating vehicle availability:', error);
            // Don't set fallback value - keep current value or show error
            const availableVehicles = document.getElementById('availableVehicles');
            if (availableVehicles) {
                availableVehicles.classList.remove('dynamic-availability');
                availableVehicles.style.borderColor = '';
                availableVehicles.style.backgroundColor = '';
            }
            
            // Show error message to user
            this.showErrorMessage('Failed to load vehicle availability. Please check your vehicle management setup.');
        }
    }

    updateCapacityLabel(vehicleTypes) {
        // Update the capacity label to show the max limit
        const capacityLabel = document.getElementById('capacityLabel');
        if (capacityLabel && vehicleTypes) {
            // Check if this is a regular package and filter vehicle types accordingly
            const isRegularPackage = this.selectedPackage && this.selectedPackage.type === 'regular';
            
            // Get the actual vehicle count from vehicle management, filtered by package type
            let actualVehicleCount = 0;
            Object.entries(vehicleTypes).forEach(([type, data]) => {
                // For regular packages, only count regular vehicle types
                if (isRegularPackage && type === 'Regular') {
                    actualVehicleCount += data.total_vehicles || 0;
                }
                // For ATV/UTV packages, only count ATV/UTV vehicle types
                else if (!isRegularPackage && type !== 'Regular') {
                    actualVehicleCount += data.total_vehicles || 0;
                }
            });
            
            if (actualVehicleCount > 0) {
                capacityLabel.innerHTML = `Available Vehicles <small class="text-success">(Dynamic)</small> <small class="text-muted">Max: ${actualVehicleCount}</small>`;
            } else {
                capacityLabel.innerHTML = 'Available Vehicles <small class="text-success">(Dynamic)</small>';
            }
        }
    }

    updateVehicleTypeBreakdown(vehicleTypes) {
        // Create or update a breakdown display for package vehicles
        let breakdownHtml = '<div class="vehicle-breakdown mt-2">';
        breakdownHtml += '<small class="text-muted">Package Vehicle Breakdown:</small><br>';
        
        // Check if this is a regular package and filter vehicle types accordingly
        const isRegularPackage = this.selectedPackage && this.selectedPackage.type === 'regular';
        
        Object.entries(vehicleTypes).forEach(([type, data]) => {
            // For regular packages, only show regular vehicle types
            if (isRegularPackage && type !== 'Regular') {
                return;
            }
            
            // For ATV/UTV packages, only show ATV/UTV vehicle types
            if (!isRegularPackage && (type === 'Regular')) {
                return;
            }
            
            const statusClass = data.available_vehicles > 0 ? 'text-success' : 'text-danger';
            breakdownHtml += `<small class="${statusClass}">${type}: ${data.available_vehicles}/${data.total_vehicles} available (${data.booked_vehicles} booked)</small><br>`;
        });
        
        breakdownHtml += '</div>';

        // Find or create the breakdown container
        let breakdownContainer = document.getElementById('vehicleBreakdown');
        if (!breakdownContainer) {
            breakdownContainer = document.createElement('div');
            breakdownContainer.id = 'vehicleBreakdown';
            const availableVehiclesContainer = document.querySelector('.card.mb-3.p-3.bg-light');
            if (availableVehiclesContainer) {
                availableVehiclesContainer.appendChild(breakdownContainer);
            }
        }
        
        breakdownContainer.innerHTML = breakdownHtml;
    }

    async updateAvailability() {
        if (!this.selectedDate || !this.selectedVariant) {
            this.showErrorMessage('Please select a date and package variant');
            return;
        }

        // Validate form data
        const availableVehicles = parseInt(document.getElementById('availableVehicles')?.value) || 0;
        const specialPriceEnabled = document.getElementById('specialPriceSwitch')?.checked || false;

        if (availableVehicles < 0) {
            this.showErrorMessage('Available vehicles cannot be negative');
            return;
        }

        const formData = {
            package_variant_id: this.selectedVariant,
            date: this.selectedDate,
            is_day_off: document.getElementById('dayOffSwitch')?.checked || false,
            capacity_total: availableVehicles,
            special_price_enabled: specialPriceEnabled,
            price_tag: document.querySelector('input[name="priceOption"]:checked')?.value || 'premium'
        };

        try {
            this.setLoadingState('formSubmission', true);
            this.showLoadingMessage('Updating availability...');

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                throw new Error('CSRF token not found. Please refresh the page.');
            }

            const response = await fetch('/admin/calendar/availability/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData),
                credentials: 'same-origin'
            });

            if (!response.ok) {
                let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
                try {
                    const errorData = await response.json();
                    errorMessage = errorData.error || errorData.message || errorMessage;
                } catch (e) {
                    const errorText = await response.text();
                    errorMessage = errorText || errorMessage;
                }
                throw new Error(errorMessage);
            }

            const result = await response.json();
            
            if (result.success) {
                this.showSuccessMessage(result.message || 'Availability updated successfully');
                
                // Reload fresh data from server to ensure consistency
                await this.loadPackageData(this.selectedPackage.id);
                
                // Reload availability for the current date
                await this.loadAvailabilityForDate(this.selectedDate);
                
                // Refresh calendar to show updated colors
                await this.refreshCalendarForDate(this.selectedDate);
            } else {
                throw new Error(result.message || 'Update failed');
            }
        } catch (error) {
            console.error('Error updating availability:', error);
            this.showErrorMessage(`Failed to update availability: ${error.message}`);
        } finally {
            this.setLoadingState('formSubmission', false);
        }
    }

    // Helper methods
    getDateStatus(dateString) {
        console.log(`Getting status for date: ${dateString}`);
        console.log('Calendar data for this date:', this.calendarData[dateString]);
        
        if (!this.calendarData[dateString]) {
            console.log(`No calendar data for ${dateString}, returning 'available'`);
            return 'available';
        }
        
        const data = this.calendarData[dateString];
        
        // Check for day-off first
        if (data.availability && data.availability.is_day_off) {
            console.log(`Date ${dateString} is day-off`);
            return 'day-off';
        }
        
        // Check for price overrides (premium/discounted)
        if (data.price_override && data.price_override.price_tag) {
            console.log(`Date ${dateString} has price override: ${data.price_override.price_tag}`);
            return data.price_override.price_tag;
        }
        
        // Check for booked status (any reserved capacity)
        if (data.availability && data.availability.capacity_reserved > 0) {
            console.log(`Date ${dateString} is booked`);
            return 'booked';
        }
        
        console.log(`Date ${dateString} is available`);
        return 'available';
    }

    getDateClasses(isCurrentMonth, isToday, status, dateString) {
        const classes = [];
        
        if (!isCurrentMonth) classes.push('disabled');
        if (isToday) classes.push('today');
        if (status !== 'available') classes.push(status);
        
        // Add selected class if this is the selected date
        if (this.selectedDate && this.selectedDate === dateString) {
            classes.push('selected');
        }
        
        // Add range selection classes
        if (this.rangeSelectionMode && this.rangeStartDate && this.rangeEndDate) {
            if (dateString === this.rangeStartDate) {
                classes.push('range-start');
            } else if (dateString === this.rangeEndDate) {
                classes.push('range-end');
            } else if (this.isDateInRange(dateString)) {
                classes.push('range-middle');
            }
        } else if (this.rangeSelectionMode && this.rangeStartDate && !this.rangeEndDate) {
            if (dateString === this.rangeStartDate) {
                classes.push('range-start');
            }
        }
        
        return classes.join(' ');
    }

    getDateIndicator(status) {
        if (status === 'available') return '';
        if (status === 'premium') {
            return `<div class="date-indicator" style="background-color: #28a745; width: 20px; height: 4px; border-radius: 0; position: absolute; bottom: 1px; left: 50%; transform: translateX(-50%); border: 1px solid #28a745;"></div>`;
        }
        if (status === 'discounted') {
            return `<div class="date-indicator" style="background-color: #6f42c1; width: 20px; height: 4px; border-radius: 0; position: absolute; bottom: 1px; left: 50%; transform: translateX(-50%); border: 1px solid #6f42c1;"></div>`;
        }
        return '';
    }

    updateAvailableSlots() {
        const availableVehicles = document.getElementById('availableVehicles');
        const availableSlots = document.getElementById('availableSlots');
        
        if (!availableVehicles || !availableSlots) return;
        
        const vehicleCount = parseInt(availableVehicles.value) || 0;
        
        // Update the available slots display
        availableSlots.textContent = vehicleCount;
        
        // Update booked slots if we have the data
        if (this.currentAvailability && this.currentAvailability.capacity_reserved !== undefined) {
            const bookedSlots = document.getElementById('bookedSlots');
            if (bookedSlots) {
                bookedSlots.textContent = this.currentAvailability.capacity_reserved;
            }
        }
    }

    async saveAvailableVehicles() {
        if (!this.selectedDate || !this.selectedVariant) {
            this.showErrorMessage('Please select a date and package variant');
            return;
        }

        const availableVehicles = document.getElementById('availableVehicles');
        if (!availableVehicles) return;

        const vehicleCount = parseInt(availableVehicles.value) || 0;
        
        // Validate input
        if (vehicleCount < 0) {
            this.showErrorMessage('Available vehicles cannot be negative');
            availableVehicles.value = 0;
            this.updateAvailableSlots();
            return;
        }
        
        // Get the actual vehicle count from vehicle management (before any manual overrides)
        let actualVehicleCount = 0;
        if (this.currentVehicleAvailability && this.currentVehicleAvailability.vehicle_types) {
            // Sum up all vehicle types to get total actual vehicles
            actualVehicleCount = Object.values(this.currentVehicleAvailability.vehicle_types)
                .reduce((total, typeData) => total + (typeData.total_vehicles || 0), 0);
        }
        
        // Debug logging
        console.log('Validation data:', {
            vehicleCount,
            actualVehicleCount,
            currentVehicleAvailability: this.currentVehicleAvailability,
            vehicleTypes: this.currentVehicleAvailability?.vehicle_types
        });
        
        // If we have actual vehicle count data, validate against it
        if (actualVehicleCount > 0 && vehicleCount > actualVehicleCount) {
            this.showErrorMessage(`Available vehicles cannot exceed your actual vehicle count (${actualVehicleCount}). You only have ${actualVehicleCount} vehicles in your vehicle management system.`);
            availableVehicles.value = actualVehicleCount;
            this.updateAvailableSlots();
            return;
        }

        try {
            this.setLoadingState('formSubmission', true);
            this.showLoadingMessage('Saving vehicle availability...');

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                throw new Error('CSRF token not found. Please refresh the page.');
            }

            const formData = {
                package_variant_id: this.selectedVariant,
                date: this.selectedDate,
                is_day_off: document.getElementById('dayOffSwitch')?.checked || false,
                capacity_total: vehicleCount,
                special_price_enabled: document.getElementById('specialPriceSwitch')?.checked || false,
                price_tag: document.querySelector('input[name="priceOption"]:checked')?.value || 'premium'
            };

            const response = await fetch('/admin/calendar/availability/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData),
                credentials: 'same-origin'
            });

            if (!response.ok) {
                let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
                try {
                    const errorData = await response.json();
                    errorMessage = errorData.error || errorData.message || errorMessage;
                } catch (e) {
                    const errorText = await response.text();
                    errorMessage = errorText || errorMessage;
                }
                throw new Error(errorMessage);
            }

            const result = await response.json();
            
            if (result.success) {
                this.showSuccessMessage('Vehicle availability updated successfully');
                
                // Remove modified state
                if (availableVehicles) {
                    availableVehicles.classList.remove('modified');
                    availableVehicles.style.borderColor = '';
                }
                
                // Reload fresh data from server to ensure consistency
                await this.loadPackageData(this.selectedPackage.id);
                
                // Reload availability for the current date
                await this.loadAvailabilityForDate(this.selectedDate);
                
                // Refresh calendar to show updated colors
                await this.refreshCalendarForDate(this.selectedDate);
                
                // Update the display
                this.updateAvailableSlots();
            } else {
                throw new Error(result.message || 'Update failed');
            }
        } catch (error) {
            console.error('Error updating vehicle availability:', error);
            this.showErrorMessage(`Failed to update vehicle availability: ${error.message}`);
            
            // Revert to previous value if save failed
            if (this.currentAvailability && this.currentAvailability.capacity_total !== undefined) {
                availableVehicles.value = this.currentAvailability.capacity_total;
                this.updateAvailableSlots();
            }
        } finally {
            this.setLoadingState('formSubmission', false);
        }
    }

    toggleSpecialPriceFields(enabled) {
        const priceFields = document.querySelectorAll('.price-field');
        priceFields.forEach(field => {
            field.style.display = enabled ? 'block' : 'none';
        });
        
        // Always enable editing of pricing cards - users should be able to edit both base prices and special prices
        this.togglePricingCardEditing(true);
    }

    togglePricingCardEditing(enabled) {
        const pricingCards = document.querySelectorAll('.pricing-card');
        console.log('Found pricing cards:', pricingCards.length);
        console.log('Special pricing enabled:', enabled);
        
        pricingCards.forEach((card, index) => {
            const editIcon = card.querySelector('.edit-icon');
            const priceDisplay = card.querySelector('.price-display');
            const specialPriceIndicator = card.querySelector('.special-price-indicator');
            
            console.log(`Card ${index}:`, {
                hasEditIcon: !!editIcon,
                hasPriceDisplay: !!priceDisplay,
                hasSpecialPriceIndicator: !!specialPriceIndicator,
                enabled: enabled,
                editIconClasses: editIcon ? editIcon.className : 'none'
            });
            
            // Always enable editing - users should be able to edit both base prices and special prices
            if (editIcon) {
                editIcon.classList.remove('d-none');
                console.log(`Card ${index}: Edit icon enabled, classes:`, editIcon.className);
            }
            if (priceDisplay) {
                priceDisplay.style.cursor = 'pointer';
                priceDisplay.title = 'Double-click to edit price';
            }
            
            // Show/hide special price indicator based on special pricing toggle and actual override
            if (specialPriceIndicator) {
                if (enabled && this.currentPriceOverride && this.currentPriceOverride.price_amount) {
                    specialPriceIndicator.classList.remove('d-none');
                } else {
                    specialPriceIndicator.classList.add('d-none');
                }
            }
        });
    }

    async handleDayOffToggle(checked) {
        const capacityField = document.getElementById('availableVehicles');
        
        if (checked) {
            if (capacityField) {
                capacityField.value = 0;
                capacityField.disabled = true;
            }
        } else {
            if (capacityField) {
                capacityField.disabled = false;
                // When disabling day off, restore to the actual vehicle count from vehicle management
                // or set to a reasonable default
                if (this.currentVehicleAvailability && this.currentVehicleAvailability.vehicle_types) {
                    const actualVehicleCount = Object.values(this.currentVehicleAvailability.vehicle_types)
                        .reduce((total, typeData) => total + (typeData.total_vehicles || 0), 0);
                    capacityField.value = actualVehicleCount;
                } else {
                    // Fallback to a reasonable default
                    capacityField.value = 6; // Default ATV count
                }
            }
        }
        
        // Update the display
        this.updateAvailableSlots();
        
        // Save the day off state to the database
        if (this.selectedDate && this.selectedVariant) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) {
                    throw new Error('CSRF token not found');
                }

                const formData = {
                    package_variant_id: this.selectedVariant,
                    date: this.selectedDate,
                    is_day_off: checked,
                    capacity_total: parseInt(capacityField?.value) || 0,
                    special_price_enabled: document.getElementById('specialPriceSwitch')?.checked || false,
                    price_tag: document.querySelector('input[name="priceOption"]:checked')?.value || 'premium'
                };

                const response = await fetch('/admin/calendar/availability/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData),
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const result = await response.json();
                
                if (result.success) {
                    this.showSuccessMessage('Day off status updated successfully');
                    
                    // Reload data to ensure consistency
                    await this.loadAvailabilityForDate(this.selectedDate);
                    await this.refreshCalendarForDate(this.selectedDate);
                } else {
                    throw new Error(result.message || 'Update failed');
                }
            } catch (error) {
                console.error('Error updating day off status:', error);
                this.showErrorMessage(`Failed to update day off status: ${error.message}`);
                
                // Revert the toggle if save failed
                const dayOffSwitch = document.getElementById('dayOffSwitch');
                if (dayOffSwitch) {
                    dayOffSwitch.checked = !checked;
                }
            }
        }
    }

    async handleSpecialPriceToggle(enabled) {
        console.log('Special price toggle changed:', enabled);
        this.toggleSpecialPriceFields(enabled);
        
        // Don't automatically apply pricing - user must click "Apply to Range" button
        // This gives user control over when to apply changes
        
        // Refresh calendar to show updated colors when special pricing is toggled
        if (this.selectedDate) {
            await this.refreshCalendarForDate(this.selectedDate);
        }
    }

    async handlePriceOptionChange(priceTag) {
        console.log('Price option changed to:', priceTag);
        
        // Don't automatically apply pricing - user must click "Apply to Range" button
        // This gives user control over when to apply changes
    }

    async handlePackageChange(packageId) {
        try {
            if (!packageId) {
                this.showErrorMessage('Please select a valid package');
                return;
            }

            // Always fetch the full package (with variants) first
            await this.loadPackageData(packageId);

            // Now, with fresh package data, set default variant
            if (this.selectedPackage && this.selectedPackage.variants && this.selectedPackage.variants.length > 0) {
                this.selectedVariant = this.selectedPackage.variants[0].id;
            } else {
                this.selectedVariant = null;
            }

            // Apply UI state that depends on the loaded package type
            this.handleInitialPackageState();

            // Reset to today and refresh side panel with accurate data
            const today = this.getTodayString();
            this.selectedDate = today;
            this.updateCalendarSelection(today);
            await this.loadAvailabilityForDate(today);
            await this.updatePricingCards(); // Update pricing cards based on selected date
        } catch (error) {
            console.error('Error handling package change:', error);
            this.showErrorMessage(`Failed to change package: ${error.message}`);
        }
    }

    previousMonth() {
        this.currentMonth.setMonth(this.currentMonth.getMonth() - 1);
        this.generateCalendar();
        if (this.selectedPackage) {
            this.loadPackageData(this.selectedPackage.id);
        }
    }

    nextMonth() {
        this.currentMonth.setMonth(this.currentMonth.getMonth() + 1);
        this.generateCalendar();
        if (this.selectedPackage) {
            this.loadPackageData(this.selectedPackage.id);
        }
    }

    updateCalendarSelection(date) {
        // Remove previous selection
        document.querySelectorAll('.date.selected').forEach(el => {
            el.classList.remove('selected');
        });
        
        // Remove range highlighting
        document.querySelectorAll('.date.range-start, .date.range-end, .date.range-middle').forEach(el => {
            el.classList.remove('range-start', 'range-end', 'range-middle');
        });
        
        // Add selection to current date
        const dateElement = document.querySelector(`[data-date="${date}"]`);
        if (dateElement) {
            dateElement.classList.add('selected');
        }
        
        // Add range highlighting if in range selection mode
        if (this.rangeSelectionMode) {
            this.highlightRangeSelection();
        }
    }

    highlightRangeSelection() {
        if (!this.rangeStartDate) return;
        
        // Highlight start date
        const startElement = document.querySelector(`[data-date="${this.rangeStartDate}"]`);
        if (startElement) {
            startElement.classList.add('range-start');
        }
        
        // Highlight end date and middle dates if end date is set
        if (this.rangeEndDate) {
            const endElement = document.querySelector(`[data-date="${this.rangeEndDate}"]`);
            if (endElement) {
                endElement.classList.add('range-end');
            }
            
            // Highlight middle dates
            const dates = this.getSelectedDates();
            dates.forEach(dateString => {
                if (dateString !== this.rangeStartDate && dateString !== this.rangeEndDate) {
                    const middleElement = document.querySelector(`[data-date="${dateString}"]`);
                    if (middleElement) {
                        middleElement.classList.add('range-middle');
                    }
                }
            });
        }
    }

    updateCalendarColors() {
        // This will be called after updating availability to refresh colors
        if (this.selectedPackage) {
            // Reload the specific date data to update calendar colors
            this.loadAvailabilityForDate(this.selectedDate).then(() => {
                // Also reload the full package data to ensure consistency
                this.loadPackageData(this.selectedPackage.id).then(() => {
                    // Regenerate calendar with updated data
                    this.generateCalendar();
                    // Restore selection if there was a selected date
                    if (this.selectedDate) {
                        this.updateCalendarSelection(this.selectedDate);
                    }
                });
            });
        }
    }

    updateMonthDisplay() {
        const monthName = this.currentMonth.toLocaleString('default', { month: 'long' });
        const year = this.currentMonth.getFullYear();
        
        const currentMonthElement = document.getElementById('currentMonth');
        if (currentMonthElement) {
            currentMonthElement.textContent = `${monthName} ${year}`;
        }
        
        // Update second month display
        const nextMonth = new Date(this.currentMonth);
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        const nextMonthName = nextMonth.toLocaleString('default', { month: 'long' });
        const nextYear = nextMonth.getFullYear();
        
        const nextMonthElement = document.getElementById('nextMonth');
        if (nextMonthElement) {
            nextMonthElement.textContent = `${nextMonthName} ${nextYear}`;
        }
    }

    getTodayString() {
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    updateDateDisplay(date) {
        const selectedDateElement = document.getElementById('selectedDate');
        if (selectedDateElement) {
            const formattedDate = this.formatDate(date);
            selectedDateElement.textContent = formattedDate;
        }
    }

    async updatePricingCards() {
        if (!this.selectedPackage || !this.selectedPackage.variants) return;
        
        // Check if we have a range selected
        const hasRange = this.rangeStartDate && this.rangeEndDate;
        
        if (hasRange) {
            // For range selection, show both weekday and weekend pricing
            await this.updatePricingCardsForRange();
        } else {
            // For single date selection, show only relevant pricing
            this.updatePricingCardsForSingleDate();
        }
    }

    async updatePricingCardsForRange() {
        // Get the dates in the selected range
        const dates = this.getSelectedDates();
        const hasWeekdays = dates.some(date => !this.isWeekendDate(date));
        const hasWeekends = dates.some(date => this.isWeekendDate(date));
        
        // Show only the relevant pricing sections based on the range content
        const weekdaySection = document.getElementById('weekdayPricingSection');
        const weekendSection = document.getElementById('weekendPricingSection');
        
        if (weekdaySection) {
            weekdaySection.style.display = hasWeekdays ? 'block' : 'none';
        }
        if (weekendSection) {
            weekendSection.style.display = hasWeekends ? 'block' : 'none';
        }
        
        // Show range indicators only for visible sections
        const weekdayIndicator = document.getElementById('weekdayRangeIndicator');
        const weekendIndicator = document.getElementById('weekendRangeIndicator');
        
        if (weekdayIndicator) {
            weekdayIndicator.style.display = hasWeekdays ? 'inline-block' : 'none';
        }
        if (weekendIndicator) {
            weekendIndicator.style.display = hasWeekends ? 'inline-block' : 'none';
        }
        
        // Update only the relevant pricing sections
        if (hasWeekdays) {
            await this.updatePricingSectionForRange('weekdayPricingCards', 'weekday');
        }
        if (hasWeekends) {
            await this.updatePricingSectionForRange('weekendPricingCards', 'weekend');
        }
        
        // Apply special pricing toggle state to the updated cards
        const specialPriceEnabled = document.getElementById('specialPriceSwitch')?.checked || false;
        this.togglePricingCardEditing(specialPriceEnabled);
    }

    updatePricingCardsForSingleDate() {
        console.log('updatePricingCardsForSingleDate called for date:', this.selectedDate);
        
        // Hide range indicators for single date selection
        const weekdayIndicator = document.getElementById('weekdayRangeIndicator');
        const weekendIndicator = document.getElementById('weekendRangeIndicator');
        
        if (weekdayIndicator) weekdayIndicator.style.display = 'none';
        if (weekendIndicator) weekendIndicator.style.display = 'none';
        
        // Set currentPriceOverride from calendar data cache
        const calendarData = this.calendarData[this.selectedDate];
        console.log('Calendar data from cache:', calendarData);
        
        if (calendarData && calendarData.price_override) {
            this.currentPriceOverride = {
                price_override: calendarData.price_override,
                all_price_overrides: calendarData.price_override ? [calendarData.price_override] : []
            };
            console.log('Set currentPriceOverride from cache:', this.currentPriceOverride);
        } else {
            this.currentPriceOverride = null;
            console.log('No price override found in cache, set currentPriceOverride to null');
        }
        
        // Determine if selected date is weekday or weekend
        const isWeekend = this.isWeekendDate(this.selectedDate);
        const priceType = isWeekend ? 'weekend' : 'weekday';
        
        // Collect prices for the relevant day type only
        let relevantPrices = [];
        
        this.selectedPackage.variants.forEach(variant => {
            if (variant && variant.prices) {
                // Add variant name to each price object for display
                const prices = variant.prices
                    .filter(p => p.price_type === priceType)
                    .map(p => ({ ...p, variant_name: variant.variant_name }));
                
                relevantPrices = relevantPrices.concat(prices);
            }
        });
        
        // Apply price overrides if they exist for this date
        this.applyPriceOverrides(relevantPrices);
        
        // Show/hide pricing sections based on selected date
        this.togglePricingSections(isWeekend);
        
        // Update the relevant pricing section
        if (isWeekend) {
            this.updatePricingSection('weekendPricingCards', relevantPrices);
        } else {
            this.updatePricingSection('weekdayPricingCards', relevantPrices);
        }
        
        // Apply special pricing toggle state to the updated cards
        const specialPriceEnabled = document.getElementById('specialPriceSwitch')?.checked || false;
        this.togglePricingCardEditing(specialPriceEnabled);
    }

    async updatePricingSectionForRange(containerId, priceType) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        // Collect prices for the specific day type
        let relevantPrices = [];
        
        this.selectedPackage.variants.forEach(variant => {
            if (variant && variant.prices) {
                // Add variant name to each price object for display
                const prices = variant.prices
                    .filter(p => p.price_type === priceType)
                    .map(p => ({ ...p, variant_name: variant.variant_name }));
                
                relevantPrices = relevantPrices.concat(prices);
            }
        });
        
        // Check if we have applied range prices stored
        if (this.appliedRangePrices && this.appliedRangePrices[priceType]) {
            // Use the applied range prices instead of base prices
            relevantPrices = relevantPrices.map(price => {
                const appliedPrice = this.appliedRangePrices[priceType][price.package_variant_id];
                if (appliedPrice !== undefined) {
                    return {
                        ...price,
                        amount: appliedPrice,
                        has_override: true,
                        override_tag: appliedPrice < price.amount ? 'discounted' : 'premium'
                    };
                }
                return price;
            });
        } else {
            // Load existing price overrides for the selected range
            await this.loadExistingRangePrices(relevantPrices, priceType);
        }
        
        // Update the pricing section
        this.updatePricingSection(containerId, relevantPrices);
    }

    async loadExistingRangePrices(relevantPrices, priceType) {
        if (!this.rangeStartDate || !this.rangeEndDate) return;
        
        try {
            // Get the dates in the selected range
            const dates = this.getSelectedDates();
            if (dates.length === 0) return;
            
            // Check if we have calendar data for any of the dates in the range
            let hasExistingOverrides = false;
            const existingPrices = {};
            
            // Check each date in the range for existing price overrides
            for (const date of dates) {
                const calendarData = this.calendarData[date];
                if (calendarData && calendarData.price_override) {
                    const priceOverride = calendarData.price_override;
                    
                    // Check if this override matches the current price type (weekday/weekend)
                    const dayOfWeek = new Date(date).getDay();
                    const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6); // Sunday = 0, Saturday = 6
                    const currentPriceType = isWeekend ? 'weekend' : 'weekday';
                    
                    if (currentPriceType === priceType && priceOverride.price_amount) {
                        hasExistingOverrides = true;
                        // Only store the price if we haven't seen this variant yet, or if this is a different price
                        if (!existingPrices[priceOverride.package_variant_id] || 
                            existingPrices[priceOverride.package_variant_id] !== priceOverride.price_amount) {
                            existingPrices[priceOverride.package_variant_id] = priceOverride.price_amount;
                        }
                    }
                }
            }
            
            // If we found existing overrides, apply them to the prices
            if (hasExistingOverrides) {
                relevantPrices.forEach(price => {
                    const existingPrice = existingPrices[price.package_variant_id];
                    if (existingPrice !== undefined) {
                        // Use the original amount for comparison (base price)
                        const basePrice = parseFloat(price.original_amount || price.amount);
                        const overridePrice = parseFloat(existingPrice);
                        
                        price.amount = existingPrice;
                        price.has_override = true;
                        price.override_tag = overridePrice < basePrice ? 'discounted' : 'premium';
                        
                        console.log(`Applied existing ${priceType} price for variant ${price.package_variant_id}: ${existingPrice} (base: ${basePrice}, tag: ${price.override_tag})`);
                    }
                });
            }
        } catch (error) {
            console.error('Error loading existing range prices:', error);
        }
    }

    applyPriceOverridesForRange(prices, priceType) {
        // For range selection, we need to check if any date in the range has overrides
        // This is a simplified approach - in a real scenario, you might want to show
        // the most common override or a summary
        const dates = this.getSelectedDates();
        
        // Check if any date in the range has price overrides
        let hasAnyOverride = false;
        let commonOverride = null;
        
        dates.forEach(dateString => {
            const dateData = this.calendarData[dateString];
            if (dateData && dateData.price_override) {
                hasAnyOverride = true;
                if (!commonOverride) {
                    commonOverride = dateData.price_override;
                }
            }
        });
        
        if (hasAnyOverride && commonOverride) {
            // Apply the common override to all prices
            prices.forEach(price => {
                price.amount = commonOverride.price_amount;
                price.has_override = true;
                price.override_tag = commonOverride.price_tag;
            });
        }
    }

    updatePricingSection(containerId, prices) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        // Check if this is for range pricing
        const isRangePricing = containerId.includes('weekdayPricingCards') || containerId.includes('weekendPricingCards');
        
        let html = '';
        prices.forEach(price => {
            
            // Determine icon HTML based on variant name with inline SVG
            const helmetSvg = '<svg width="22" height="20" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.4945 7.75319C14.6673 7.75319 13.9945 8.42594 13.9945 9.25319C13.9945 10.0804 14.6673 10.7532 15.4945 10.7532C16.3218 10.7532 16.9945 10.0804 16.9945 9.25319C16.9945 8.42594 16.3218 7.75319 15.4945 7.75319ZM15.4945 10.3782C14.8743 10.3782 14.3695 9.87344 14.3695 9.25319C14.3695 8.63294 14.8743 8.12819 15.4945 8.12819C16.1148 8.12819 16.6195 8.63294 16.6195 9.25319C16.6195 9.87344 16.1148 10.3782 15.4945 10.3782ZM12.9051 3.38856L10.5107 0.695312C10.1822 0.771062 9.85525 0.853563 9.53125 0.949187L11.8818 3.59369L12.9051 3.38856Z" fill="#FC692A"/><path d="M15.5 9.625C15.7071 9.625 15.875 9.45711 15.875 9.25C15.875 9.04289 15.7071 8.875 15.5 8.875C15.2929 8.875 15.125 9.04289 15.125 9.25C15.125 9.45711 15.2929 9.625 15.5 9.625Z" fill="#FC692A"/><path d="M16.685 1.23813C16.0226 0.86838 15.324 0.567315 14.6004 0.339625L14.315 0.25C13.9955 0.251125 13.6775 0.2635 13.3606 0.28225L15.4876 2.67512L16.685 1.23813ZM14.7823 3.01075L12.4291 0.363625C12.0714 0.404125 11.7159 0.455875 11.3622 0.518875L13.7589 3.21512L14.7823 3.01075ZM11.0319 3.76037L8.75262 1.1965C8.50026 1.2841 8.24994 1.3775 8.00187 1.47662L7.87737 1.5265L10.0107 3.96475L11.0319 3.76037ZM5.5535 13.75C5.87975 13.75 6.17 13.6814 6.42875 13.552L11.7687 10.882L12.089 7.68212L4.50537 6.30325L2.79688 11.7704L4.2275 13.201C4.40131 13.3755 4.60798 13.5139 4.83557 13.6082C5.06317 13.7024 5.30717 13.7506 5.5535 13.75ZM10.5118 8.39012L8.63675 10.2651L8.1065 9.73487L9.9815 7.85987L10.5118 8.39012ZM8.4815 7.85987L9.01175 8.39012L7.13675 10.2651L6.6065 9.73487L8.4815 7.85987Z" fill="#FC692A"/><path d="M17.337 1.625L15.786 3.4865C15.7311 3.55212 15.6554 3.59709 15.5715 3.614L9.9465 4.739C9.88175 4.75195 9.81472 4.74761 9.75218 4.72641C9.68964 4.70521 9.63378 4.66791 9.59025 4.61825L7.116 1.79075L0.140625 3.5345C0.211634 4.07649 0.477135 4.57422 0.88772 4.93506C1.2983 5.29591 1.82601 5.4953 2.37263 5.49612H4.24763C4.27013 5.49612 4.29262 5.498 4.31475 5.50213L12.5648 7.00213C12.6575 7.019 12.7404 7.07015 12.7971 7.1454C12.8538 7.22065 12.8801 7.31449 12.8708 7.40825L12.4958 11.1582C12.4894 11.2212 12.4673 11.2816 12.4314 11.3338C12.3954 11.3859 12.3469 11.4281 12.2903 11.4564L6.76538 14.219C6.40081 14.4009 5.99904 14.4957 5.59163 14.4961C4.85363 14.4961 4.19438 14.2231 3.69825 13.7274L2.475 12.5041L1.62263 14.2096V18.6211C1.62263 19.2414 2.12738 19.7461 2.74763 19.7461H2.87925C3.00038 19.7461 3.12037 19.7266 3.2355 19.6884L18.5588 14.5805L19.2589 11.78C19.2728 11.7239 19.2995 11.6718 19.337 11.6278C19.3745 11.5838 19.4217 11.5491 19.4749 11.5265L21.8726 10.499V7.43262C21.054 5.01987 19.4348 2.98287 17.337 1.625ZM15.4976 11.4961C14.2568 11.4961 13.2476 10.487 13.2476 9.24613C13.2476 8.00525 14.2568 6.99613 15.4976 6.99613C16.7385 6.99613 17.7476 8.00525 17.7476 9.24613C17.7476 10.487 16.7385 11.4961 15.4976 11.4961Z" fill="#FC692A"/></svg>';
            
            let iconHtml = '';
            if (price.variant_name === 'Double Rider') {
                iconHtml = `<div class="d-flex">${helmetSvg.replace('<svg', '<svg style="height: 40px; width: 30px;"')}${helmetSvg.replace('<svg', '<svg style="height: 40px; width: 30px;"')}</div>`;
            } else {
                iconHtml = helmetSvg.replace('<svg', '<svg style="height: 40px; width: 40px;"');
            }
            
            // Check if this price has an override
            const hasOverride = price.has_override || false;
            const overrideTag = price.override_tag || null;
            
            html += `
                <div class="card pricing-card mb-3" data-price-id="${price.id}" data-variant-id="${price.package_variant_id}">
                    <div class="d-flex align-items-stretch pricing-card-flex">
                        <div class="d-flex flex-column align-items-center justify-content-center p-3 pricing-card-header">
                            ${iconHtml}
                            <span class="${price.variant_name === 'Double Rider' ? 'mt-2' : ''}">${price.variant_name || 'Rider'}</span>
                        </div>
                        <div class="d-flex flex-grow-1 align-items-center justify-content-center pricing-card-body">
                            <div class="text-end me-2">
                                <div class="fw-bold">TK</div>
                            </div>
                            <div class="text-start">
                                ${hasOverride ? `
                                    ${overrideTag === 'discounted' ? `
                                        <div class="special-price-container">
                                            <div class="fs-2 fw-bold text-dark price-display special-price" data-price-id="${price.id}" ondblclick="calendarManager.startEditPrice(${price.id})">${price.amount}</div>
                                            <div class="original-price-crossed">TK ${price.original_amount || price.amount}</div>
                                            <div class="special-price-indicator" style="font-size: 0.75rem; color: #FC692A;">Special Price</div>
                                        </div>
                                    ` : `
                                        <div class="regular-price-container">
                                            <div class="fs-2 fw-bold text-dark price-display" data-price-id="${price.id}" ondblclick="calendarManager.startEditPrice(${price.id})">${price.amount}</div>
                                            <div class="base-price" style="font-size: 0.75rem; color: #6c757d;">Premium Price</div>
                                        </div>
                                    `}
                                ` : `
                                    <div class="regular-price-container">
                                        <div class="fs-2 fw-bold text-dark price-display" data-price-id="${price.id}" ${isRangePricing ? `ondblclick="calendarManager.startEditRangePrice(${price.id}, '${containerId}')"` : `ondblclick="calendarManager.startEditPrice(${price.id})"`}>${price.amount}</div>
                                        <div class="base-price" style="font-size: 0.75rem; color: #6c757d;">Base: TK ${price.original_amount || price.amount}</div>
                                    </div>
                                `}
                                <input type="number" class="form-control price-input d-none" 
                                       data-price-id="${price.id}" 
                                       value="${price.amount}" 
                                       step="0.01" 
                                       min="0" 
                                       style="width: 120px; font-size: 1.5rem; font-weight: bold;">
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-center pricing-card-footer">
                            <span class="edit-icon d-none" onclick="${isRangePricing ? `calendarManager.startEditRangePrice(${price.id}, '${containerId}')` : `calendarManager.startEditPrice(${price.id})`}">✏</span>
                            <span class="save-icon d-none" onclick="${isRangePricing ? `calendarManager.saveRangePrice(${price.id})` : `calendarManager.savePrice(${price.id})`}">✓</span>
                            <span class="cancel-icon d-none" onclick="${isRangePricing ? `calendarManager.cancelRangePrice(${price.id})` : `calendarManager.cancelEditPrice(${price.id})`}">✕</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }

    applyPriceOverrides(prices) {
        console.log('applyPriceOverrides called with prices:', prices);
        console.log('this.currentPriceOverride:', this.currentPriceOverride);
        
        // Get all price overrides for the selected date from the backend data
        const allPriceOverrides = this.currentPriceOverride?.all_price_overrides || [];
        console.log('allPriceOverrides:', allPriceOverrides);
        
        if (!allPriceOverrides || allPriceOverrides.length === 0) {
            console.log('No price overrides to apply');
            return; // No price overrides to apply
        }

        // Apply the price overrides to each price individually based on its variant
        prices.forEach(price => {
            // original_amount is already set in updatePricingCards
            // Don't overwrite it here
            
            // Find the specific price override for this variant
            const variantOverride = allPriceOverrides.find(override => 
                override.package_variant_id === price.package_variant_id
            );
            
            console.log(`Price ID ${price.id} (variant ${price.package_variant_id}):`, {
                variantOverride: variantOverride,
                hasOverride: !!(variantOverride && variantOverride.price_amount)
            });
            
            if (variantOverride && variantOverride.price_amount) {
                // Apply the override amount for this specific variant
                price.amount = variantOverride.price_amount;
                price.has_override = true;
                price.override_tag = variantOverride.price_tag;
                console.log(`Applied override to price ID ${price.id}: amount=${price.amount}, tag=${price.override_tag}`);
            } else {
                // No override for this variant
                price.has_override = false;
                console.log(`No override for price ID ${price.id}`);
            }
        });
    }

    updateCalendarDataCache(dateString, data) {
        console.log(`Updating calendar data cache for ${dateString}:`, data);
        
        // Initialize calendar data for this date if it doesn't exist
        if (!this.calendarData[dateString]) {
            this.calendarData[dateString] = {};
        }
        
        // Update the calendar data with availability and price override information
        this.calendarData[dateString].availability = data.availability || null;
        this.calendarData[dateString].price_override = data.price_override || null;
        
        // Calculate and store the status for color coding
        if (data.price_override && data.price_override.price_tag) {
            this.calendarData[dateString].status = data.price_override.price_tag;
        } else if (data.availability && data.availability.is_day_off) {
            this.calendarData[dateString].status = 'day-off';
        } else if (data.availability && data.availability.capacity_reserved > 0) {
            this.calendarData[dateString].status = 'booked';
        } else {
            this.calendarData[dateString].status = 'available';
        }
        
        console.log(`Calendar data cache updated for ${dateString}:`, {
            availability: data.availability,
            price_override: data.price_override,
            status: this.calendarData[dateString].status
        });
        console.log('Updated calendar data:', this.calendarData[dateString]);
    }

    async refreshCalendarForDate(dateString) {
        try {
            console.log(`Starting calendar refresh for date: ${dateString}`);
            console.log('Current calendar data:', this.calendarData[dateString]);
            
            // Update the calendar data for this specific date
            const status = this.getDateStatus(dateString);
            console.log(`Calculated status for ${dateString}: ${status}`);
            
            // Update the calendar data cache
            if (!this.calendarData[dateString]) {
                this.calendarData[dateString] = {};
            }
            
            // Update the status in the cache
            this.calendarData[dateString].status = status;
            
            // Regenerate the calendar to reflect the new status
            this.generateCalendar();
            
            // Re-apply the current selection
            this.updateCalendarSelection(this.selectedDate);
            
            console.log(`Calendar refreshed for date ${dateString} with status: ${status}`);
            console.log('Updated calendar data:', this.calendarData[dateString]);
        } catch (error) {
            console.error('Error refreshing calendar for date:', error);
        }
    }

    isToday(date) {
        const today = new Date();
        return date.getDate() === today.getDate() && 
               date.getMonth() === today.getMonth() && 
               date.getFullYear() === today.getFullYear();
    }

    isWeekendDate(dateString) {
        const date = new Date(dateString);
        const dayOfWeek = date.getDay();
        // 0 = Sunday, 1 = Monday, 2 = Tuesday, 3 = Wednesday, 4 = Thursday, 5 = Friday, 6 = Saturday
        // Weekend pricing applies to Friday (5) and Saturday (6)
        return dayOfWeek === 5 || dayOfWeek === 6;
    }

    togglePricingSections(isWeekend) {
        const weekdaySection = document.getElementById('weekdayPricingSection');
        const weekendSection = document.getElementById('weekendPricingSection');
        
        if (isWeekend) {
            // Show weekend section, hide weekday section
            if (weekdaySection) weekdaySection.style.display = 'none';
            if (weekendSection) weekendSection.style.display = 'block';
        } else {
            // Show weekday section, hide weekend section
            if (weekdaySection) weekdaySection.style.display = 'block';
            if (weekendSection) weekendSection.style.display = 'none';
        }
    }

    formatDate(dateString) {
        // Parse the date string and ensure it's treated as local time, not UTC
        const [year, month, day] = dateString.split('-').map(Number);
        const date = new Date(year, month - 1, day); // month is 0-indexed
        
        return date.toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        }).replace(',', '');
    }

    handleInitialPackageState() {
        // Handle package-specific UI state
        const capacityField = document.getElementById('availableVehicles');
        const dayOffSwitch = document.getElementById('dayOffSwitch');
        const capacityLabel = document.getElementById('capacityLabel');
        const capacitySection = document.querySelector('.card.mb-3.p-3.bg-light').closest('.card');
        
        // Show capacity section for all package types
        if (capacitySection) {
            capacitySection.style.display = 'block';
        }
        
        // Update capacity label (will be updated when vehicle availability is loaded)
        if (capacityLabel) {
            capacityLabel.innerHTML = 'Available Vehicles <small class="text-success">(Dynamic)</small>';
        }
        
        // Ensure capacity field is enabled
        if (capacityField) {
            capacityField.disabled = false;
        }
        
        // Handle day off switch functionality
        if (dayOffSwitch && dayOffSwitch.checked) {
            // If day off is checked, disable capacity field
            if (capacityField) {
                capacityField.value = 0;
                capacityField.disabled = true;
            }
        } else {
            // If day off is not checked, ensure capacity field is enabled
            if (capacityField) {
                capacityField.disabled = false;
            }
        }
    }

    // Inline Edit Methods for Pricing
    startEditRangePrice(priceId, containerId) {
        console.log('startEditRangePrice called with priceId:', priceId, 'containerId:', containerId);
        
        // Check if we're in range selection mode
        if (!this.rangeStartDate || !this.rangeEndDate) {
            this.showErrorMessage('Please select a date range first');
            return;
        }
        
        const priceDisplay = document.querySelector(`.price-display[data-price-id="${priceId}"]`);
        const priceInput = document.querySelector(`.price-input[data-price-id="${priceId}"]`);
        const editIcon = document.querySelector(`.edit-icon[onclick*="${priceId}"]`);
        const saveIcon = document.querySelector(`.save-icon[onclick*="${priceId}"]`);
        const cancelIcon = document.querySelector(`.cancel-icon[onclick*="${priceId}"]`);

        if (priceDisplay && priceInput && editIcon && saveIcon && cancelIcon) {
            // Hide display, show input
            priceDisplay.classList.add('d-none');
            priceInput.classList.remove('d-none');
            priceInput.focus();
            priceInput.select();
            
            // Hide edit icon, show save/cancel icons
            editIcon.classList.add('d-none');
            saveIcon.classList.remove('d-none');
            cancelIcon.classList.remove('d-none');
            
            // Store the original value for cancel functionality
            priceInput.dataset.originalValue = priceInput.value;
        }
    }

    saveRangePrice(priceId) {
        console.log('saveRangePrice called with priceId:', priceId);
        
        const priceInput = document.querySelector(`.price-input[data-price-id="${priceId}"]`);
        const priceDisplay = document.querySelector(`.price-display[data-price-id="${priceId}"]`);
        const editIcon = document.querySelector(`.edit-icon[onclick*="${priceId}"]`);
        const saveIcon = document.querySelector(`.save-icon[onclick*="${priceId}"]`);
        const cancelIcon = document.querySelector(`.cancel-icon[onclick*="${priceId}"]`);

        if (priceInput && priceDisplay && editIcon && saveIcon && cancelIcon) {
            const newValue = parseFloat(priceInput.value);
            
            if (isNaN(newValue) || newValue < 0) {
                this.showErrorMessage('Please enter a valid price');
                return;
            }
            
            // Update the display with the new value
            priceDisplay.textContent = newValue.toFixed(2);
            
            // Hide input, show display
            priceInput.classList.add('d-none');
            priceDisplay.classList.remove('d-none');
            
            // Hide save/cancel icons, show edit icon
            saveIcon.classList.add('d-none');
            cancelIcon.classList.add('d-none');
            editIcon.classList.remove('d-none');
            
            console.log('Range price updated to:', newValue);
        }
    }

    cancelRangePrice(priceId) {
        console.log('cancelRangePrice called with priceId:', priceId);
        
        const priceInput = document.querySelector(`.price-input[data-price-id="${priceId}"]`);
        const priceDisplay = document.querySelector(`.price-display[data-price-id="${priceId}"]`);
        const editIcon = document.querySelector(`.edit-icon[onclick*="${priceId}"]`);
        const saveIcon = document.querySelector(`.save-icon[onclick*="${priceId}"]`);
        const cancelIcon = document.querySelector(`.cancel-icon[onclick*="${priceId}"]`);

        if (priceInput && priceDisplay && editIcon && saveIcon && cancelIcon) {
            // Restore original value
            priceInput.value = priceInput.dataset.originalValue || priceInput.value;
            
            // Hide input, show display
            priceInput.classList.add('d-none');
            priceDisplay.classList.remove('d-none');
            
            // Hide save/cancel icons, show edit icon
            saveIcon.classList.add('d-none');
            cancelIcon.classList.add('d-none');
            editIcon.classList.remove('d-none');
        }
    }

    startEditPrice(priceId) {
        console.log('startEditPrice called with priceId:', priceId);
        
        // Check if we're in range selection mode
        if (this.rangeStartDate && this.rangeEndDate) {
            console.log('Range selection mode detected - price editing should be handled via range pricing');
            // In range selection mode, we should not allow individual price editing
            // Instead, the user should edit the prices in the range pricing sections
            this.showErrorMessage('In range selection mode, please edit prices using the Weekdays/Weekend pricing sections above, then click "Apply to Range"');
            return;
        }
        
        // Allow editing both base prices and special prices
        const specialPriceEnabled = document.getElementById('specialPriceSwitch')?.checked || false;
        console.log('Special pricing enabled:', specialPriceEnabled);

        const priceDisplay = document.querySelector(`.price-display[data-price-id="${priceId}"]`);
        const priceInput = document.querySelector(`.price-input[data-price-id="${priceId}"]`);
        const editIcon = document.querySelector(`.edit-icon[onclick*="${priceId}"]`);
        const saveIcon = document.querySelector(`.save-icon[onclick*="${priceId}"]`);
        const cancelIcon = document.querySelector(`.cancel-icon[onclick*="${priceId}"]`);

        if (priceDisplay && priceInput && editIcon && saveIcon && cancelIcon) {
            // Hide display, show input
            priceDisplay.classList.add('d-none');
            priceInput.classList.remove('d-none');
            
            // Hide edit icon, show save/cancel icons
            editIcon.classList.add('d-none');
            saveIcon.classList.remove('d-none');
            cancelIcon.classList.remove('d-none');
            
            // Focus on input
            priceInput.focus();
            priceInput.select();

            // Add keyboard event listeners
            const handleKeyPress = (e) => {
                if (e.key === 'Enter') {
                    this.savePrice(priceId);
                    priceInput.removeEventListener('keydown', handleKeyPress);
                } else if (e.key === 'Escape') {
                    this.cancelEditPrice(priceId);
                    priceInput.removeEventListener('keydown', handleKeyPress);
                }
            };

            priceInput.addEventListener('keydown', handleKeyPress);
        }
    }

    async savePrice(priceId) {
        const priceDisplay = document.querySelector(`.price-display[data-price-id="${priceId}"]`);
        const priceInput = document.querySelector(`.price-input[data-price-id="${priceId}"]`);
        const editIcon = document.querySelector(`.edit-icon[onclick*="${priceId}"]`);
        const saveIcon = document.querySelector(`.save-icon[onclick*="${priceId}"]`);
        const cancelIcon = document.querySelector(`.cancel-icon[onclick*="${priceId}"]`);

        if (!priceInput) {
            this.showErrorMessage('Price input element not found');
            return;
        }

        const newAmount = parseFloat(priceInput.value);
        if (isNaN(newAmount) || newAmount < 0) {
            this.showErrorMessage('Please enter a valid price amount (must be a positive number)');
            return;
        }

        // Check if special pricing is enabled
        const specialPriceEnabled = document.getElementById('specialPriceSwitch')?.checked || false;
        
        // Frontend validation for special pricing - prevent saving invalid prices
        if (specialPriceEnabled) {
            const priceTag = document.querySelector('input[name="priceOption"]:checked')?.value || 'premium';
            
            // Get the base price for validation
            const pricingCard = priceInput.closest('.pricing-card');
            const basePriceElement = pricingCard.querySelector('.base-price');
            if (basePriceElement) {
                const basePriceText = basePriceElement.textContent;
                console.log('Base price text:', basePriceText);
                
                // Handle different base price formats
                let basePrice;
                if (basePriceText.includes('Base: TK ')) {
                    basePrice = parseFloat(basePriceText.replace('Base: TK ', '').replace(',', ''));
                } else if (basePriceText.includes('TK ')) {
                    basePrice = parseFloat(basePriceText.replace('TK ', '').replace(',', ''));
                } else {
                    basePrice = parseFloat(basePriceText.replace(',', ''));
                }
                
                console.log('Extracted base price:', basePrice);
                console.log('New amount:', newAmount);
                console.log('Price tag:', priceTag);
                
                if (priceTag === 'premium' && newAmount <= basePrice) {
                    this.showErrorMessage(`Premium price must be higher than the base price (TK ${basePrice.toLocaleString()})`);
                    return;
                }
                
                if (priceTag === 'discounted' && newAmount >= basePrice) {
                    this.showErrorMessage(`Discounted price must be lower than the base price (TK ${basePrice.toLocaleString()})`);
                    return;
                }
            } else {
                console.log('Base price element not found');
                // Try to find original amount from the price data
                const originalPriceElement = pricingCard.querySelector('.original-price-crossed');
                if (originalPriceElement) {
                    const originalPriceText = originalPriceElement.textContent;
                    const basePrice = parseFloat(originalPriceText.replace('TK ', '').replace(',', ''));
                    console.log('Using original price as base:', basePrice);
                    
                    if (priceTag === 'premium' && newAmount <= basePrice) {
                        this.showErrorMessage(`Premium price must be higher than the base price (TK ${basePrice.toLocaleString()})`);
                        return;
                    }
                    
                    if (priceTag === 'discounted' && newAmount >= basePrice) {
                        this.showErrorMessage(`Discounted price must be lower than the base price (TK ${basePrice.toLocaleString()})`);
                        return;
                    }
                }
            }
        }
        
        // Check if input is marked as invalid by real-time validation
        if (priceInput.classList.contains('is-invalid')) {
            const errorMessage = priceInput.title || 'Invalid price value';
            this.showErrorMessage(errorMessage);
            return;
        }
        
        // Show loading state
        saveIcon.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 1rem; height: 1rem;"></span>';
        saveIcon.style.pointerEvents = 'none';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                throw new Error('CSRF token not found. Please refresh the page.');
            }

            let response;
            if (specialPriceEnabled) {
                // Create date-specific price override
                const priceTag = document.querySelector('input[name="priceOption"]:checked')?.value || 'premium';
                
                // Get the variant ID from the pricing card
                const priceElement = document.querySelector(`.price-input[data-price-id="${priceId}"]`);
                const pricingCard = priceElement?.closest('.pricing-card');
                const variantId = pricingCard?.getAttribute('data-variant-id');
                
                const requestData = {
                    package_variant_id: variantId || this.selectedVariant,
                    date: this.selectedDate,
                    special_price_enabled: true,
                    price_tag: priceTag,
                    price_override_amount: newAmount,
                    price_override_id: priceId
                    // Note: Not sending capacity_total for price-only updates
                };
                
                console.log('Sending special price override request:', requestData);
                console.log('CSRF Token:', csrfToken);
                
                response = await fetch('/admin/calendar/availability/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData),
                    credentials: 'same-origin'
                });
            } else {
                // Update base price
                response = await fetch('/admin/calendar/update-price', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        price_id: priceId,
                        amount: newAmount
                    }),
                    credentials: 'same-origin'
                });
            }

            if (!response.ok) {
                let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
                
                // Handle redirects (302, 301, etc.)
                if (response.status >= 300 && response.status < 400) {
                    errorMessage = `Authentication/Authorization error. Please refresh the page and try again. (${response.status})`;
                    console.error('Redirect detected - likely authentication issue');
                } else {
                    try {
                        const errorData = await response.json();
                        errorMessage = errorData.error || errorData.message || errorMessage;
                    } catch (e) {
                        const errorText = await response.text();
                        errorMessage = errorText || errorMessage;
                    }
                }
                throw new Error(errorMessage);
            }

            const result = await response.json();
            
            if (result.success) {
                // Update display with new value
                priceDisplay.textContent = newAmount.toFixed(2);
                
                // Show success message
                const message = specialPriceEnabled ? 'Special price set for this date' : 'Base price updated successfully';
                this.showSuccessMessage(message);
                
                // Exit edit mode
                this.exitEditMode(priceId);
                
                // Refresh pricing cards to show updated prices
                if (specialPriceEnabled) {
                    // Reload availability data to get updated price override
                    await this.loadAvailabilityForDate(this.selectedDate);
                    
                    // Refresh calendar to show updated colors for the date
                    await this.refreshCalendarForDate(this.selectedDate);
                }
            } else {
                throw new Error(result.message || 'Update failed');
            }
        } catch (error) {
            console.error('Error updating price:', error);
            console.log('About to show error message:', error.message);
            this.showErrorMessage(`Failed to update price: ${error.message}`);
            console.log('Error message should have been shown');
        } finally {
            // Reset save icon
            saveIcon.innerHTML = '<span>✓</span>';
            saveIcon.style.pointerEvents = 'auto';
        }
    }

    cancelEditPrice(priceId) {
        const priceDisplay = document.querySelector(`.price-display[data-price-id="${priceId}"]`);
        const priceInput = document.querySelector(`.price-input[data-price-id="${priceId}"]`);
        const editIcon = document.querySelector(`.edit-icon[onclick*="${priceId}"]`);
        const saveIcon = document.querySelector(`.save-icon[onclick*="${priceId}"]`);
        const cancelIcon = document.querySelector(`.cancel-icon[onclick*="${priceId}"]`);

        if (priceDisplay && priceInput && editIcon && saveIcon && cancelIcon) {
            // Reset input value to original display value
            priceInput.value = priceDisplay.textContent;
            
            // Exit edit mode
            this.exitEditMode(priceId);
        }
    }

    exitEditMode(priceId) {
        const priceDisplay = document.querySelector(`.price-display[data-price-id="${priceId}"]`);
        const priceInput = document.querySelector(`.price-input[data-price-id="${priceId}"]`);
        const editIcon = document.querySelector(`.edit-icon[onclick*="${priceId}"]`);
        const saveIcon = document.querySelector(`.save-icon[onclick*="${priceId}"]`);
        const cancelIcon = document.querySelector(`.cancel-icon[onclick*="${priceId}"]`);

        if (priceDisplay && priceInput && editIcon && saveIcon && cancelIcon) {
            // Show display, hide input
            priceDisplay.classList.remove('d-none');
            priceInput.classList.add('d-none');
            
            // Show/hide edit icon based on special pricing toggle state
            const specialPriceEnabled = document.getElementById('specialPriceSwitch')?.checked || false;
            if (specialPriceEnabled) {
                editIcon.classList.remove('d-none');
            } else {
                editIcon.classList.add('d-none');
            }
            
            saveIcon.classList.add('d-none');
            cancelIcon.classList.add('d-none');
        }
    }

    // Reset Pricing Methods
    async resetPricing(priceType) {
        if (!this.selectedPackage || !this.selectedPackage.variants) {
            this.showErrorMessage('No package selected');
            return;
        }

        const variant = this.selectedPackage.variants[0];
        if (!variant || !variant.prices) {
            this.showErrorMessage('No pricing data available');
            return;
        }

        // Check if range is selected
        const hasRange = this.rangeStartDate && this.rangeEndDate;
        const dates = hasRange ? this.getSelectedDates() : [this.selectedDate];
        const dateCount = dates.length;

        // Show confirmation dialog
        const confirmed = confirm(
            `Are you sure you want to reset all ${priceType} prices to their original values ` +
            `and clear any special pricing for ${dateCount} date${dateCount > 1 ? 's' : ''}?`
        );
        if (!confirmed) {
            return;
        }

        // Show loading state
        let resetButton = document.querySelector(`.reset-link[onclick*="resetPricing('${priceType}')"]`) || 
                         document.querySelector(`a[onclick*="resetPricing('${priceType}')"]`) ||
                         document.querySelector(`[onclick*="resetPricing('${priceType}')"]`);
        
        // Fallback: find button by section
        if (!resetButton) {
            const sectionId = priceType === 'weekday' ? 'weekdayPricingSection' : 'weekendPricingSection';
            const section = document.getElementById(sectionId);
            if (section) {
                resetButton = section.querySelector('.reset-link') || section.querySelector('a[onclick*="resetPricing"]');
            }
        }
        
        if (resetButton) {
            const originalText = resetButton.textContent;
            resetButton.textContent = 'Resetting...';
            resetButton.style.pointerEvents = 'none';
        }

        try {
            // Use the new comprehensive reset method that clears both base prices and overrides
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                throw new Error('CSRF token not found');
            }

            let successCount = 0;
            let totalOverrides = 0;

            // Show visual feedback for range reset
            if (hasRange) {
                this.highlightRangeSelection();
                const rangeElements = document.querySelectorAll('.date.range-start, .date.range-end, .date.range-middle');
                rangeElements.forEach(el => el.style.opacity = '0.6');
            }

            // Reset pricing for each date in the range
            for (const date of dates) {
                const response = await fetch('/admin/calendar/reset-pricing', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        package_variant_id: variant.id,
                        date: date,  // Use date from loop, not this.selectedDate
                        price_type: priceType
                    })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Failed to reset pricing for ${date}: ${response.status} ${response.statusText}`);
                }

                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.message || `Reset failed for ${date}`);
                }

                successCount++;
                totalOverrides += result.overrides_cleared || 0;
            }

            // Refresh the pricing cards
            await this.updatePricingCards();
            
            // Also refresh the package data to ensure we have the latest information
            if (this.selectedPackage) {
                await this.loadPackageData(this.selectedPackage.id);
            }

            // Reload availability for the current date to refresh all data
            await this.loadAvailabilityForDate(this.selectedDate);
            
            // Turn off special pricing toggle if it was enabled
            const specialPriceSwitch = document.getElementById('specialPriceSwitch');
            if (specialPriceSwitch && specialPriceSwitch.checked) {
                specialPriceSwitch.checked = false;
                this.handleSpecialPriceToggle(false);
            }
            
            // Clear price option selection (Premium/Discounted Price buttons)
            const priceOptions = document.querySelectorAll('input[name="priceOption"]');
            priceOptions.forEach(option => {
                option.checked = false;
            });
            
            // Refresh calendar display to show updated status
            if (hasRange) {
                // Refresh calendar for all dates in range
                this.generateCalendar();
                if (this.selectedDate) {
                    this.updateCalendarSelection(this.selectedDate);
                }
            } else if (this.calendarData && this.selectedDate) {
                await this.refreshCalendarForDate(this.selectedDate);
            }
            
            this.showSuccessMessage(
                `${priceType.charAt(0).toUpperCase() + priceType.slice(1)} pricing reset ` +
                `successfully for ${successCount} date${successCount > 1 ? 's' : ''}. ` +
                `${totalOverrides} price override${totalOverrides !== 1 ? 's' : ''} cleared.`
            );
        } catch (error) {
            console.error('Reset pricing error:', error);
            this.showErrorMessage(`Failed to reset pricing: ${error.message}`);
        } finally {
            // Restore opacity for range elements
            if (hasRange) {
                const rangeElements = document.querySelectorAll('.date.range-start, .date.range-end, .date.range-middle');
                rangeElements.forEach(el => el.style.opacity = '1');
            }
            
            // Reset button state
            if (resetButton) {
                resetButton.textContent = 'Reset';
                resetButton.style.pointerEvents = 'auto';
            }
        }
    }

    async ensureOriginalAmounts() {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                console.warn('CSRF token not found, skipping original amount check');
                return;
            }

            const response = await fetch('/admin/calendar/ensure-original-amounts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            if (response.ok) {
                const result = await response.json();
                if (result.success && result.updated_count > 0) {
                    console.log(`Updated ${result.updated_count} prices with original_amount`);
                }
            }
        } catch (error) {
            console.warn('Failed to ensure original amounts:', error);
        }
    }

    async resetSinglePrice(priceId, originalAmount) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                throw new Error('CSRF token not found');
            }

            const response = await fetch('/admin/calendar/update-price', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    price_id: priceId,
                    amount: originalAmount
                })
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Failed to reset price: ${response.status} ${response.statusText}`);
            }

            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Reset failed');
            }

            return result;
        } catch (error) {
            throw error;
        }
    }

}

// Initialize calendar manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.calendarManager = new CalendarManager();
});
