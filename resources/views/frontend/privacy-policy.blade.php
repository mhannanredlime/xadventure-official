@extends('layouts.frontend')

@section('title', 'Privacy Policy - Adventour Adventure Bandarban')

@push('styles')
<style>
    .privacy-policy-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 40px 20px;
    }
    
    .privacy-policy-header {
        text-align: center;
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e66000;
    }
    
    .privacy-policy-header h1 {
        color: #e66000;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .privacy-policy-header p {
        color: #6c757d;
        font-size: 1.1rem;
    }
    
    .privacy-policy-content {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .privacy-policy-content h2 {
        color: #e66000;
        font-weight: 600;
        margin-top: 30px;
        margin-bottom: 15px;
        font-size: 1.5rem;
    }
    
    .privacy-policy-content h3 {
        color: #495057;
        font-weight: 600;
        margin-top: 25px;
        margin-bottom: 10px;
        font-size: 1.2rem;
    }
    
    .privacy-policy-content p {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 15px;
    }
    
    .privacy-policy-content ul {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 15px;
        padding-left: 20px;
    }
    
    .privacy-policy-content li {
        margin-bottom: 8px;
    }
    
    .contact-info {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #e66000;
        margin-top: 30px;
    }
    
    .contact-info h3 {
        color: #e66000;
        margin-bottom: 15px;
    }
    
    .contact-info p {
        margin-bottom: 8px;
    }
    
    .back-link {
        text-align: center;
        margin-top: 30px;
    }
    
    .back-link a {
        color: #e66000;
        text-decoration: none;
        font-weight: 500;
        padding: 10px 20px;
        border: 2px solid #e66000;
        border-radius: 25px;
        transition: all 0.3s ease;
    }
    
    .back-link a:hover {
        background: #e66000;
        color: white;
        text-decoration: none;
    }
    
    @media (max-width: 768px) {
        .privacy-policy-container {
            padding: 20px 15px;
        }
        
        .privacy-policy-content {
            padding: 20px;
        }
        
        .privacy-policy-header h1 {
            font-size: 1.8rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container maincontent-margin" style="margin-top: 80px;">
    <div class="privacy-policy-container">
        <div class="privacy-policy-header">
            <h1>Privacy Policy</h1>
            <p>Adventour Adventure Bandarban</p>
            <small class="text-muted">Last updated: {{ date('F d, Y') }}</small>
        </div>
        
        <div class="privacy-policy-content">
            <p>Welcome to Adventour Adventure Bandarban. We are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or use our services.</p>
            
            <h2>Information We Collect</h2>
            
            <h3>Personal Information</h3>
            <p>We may collect personal information that you voluntarily provide to us when you:</p>
            <ul>
                <li>Make a booking or reservation</li>
                <li>Create an account</li>
                <li>Subscribe to our newsletter</li>
                <li>Contact us for support</li>
                <li>Participate in surveys or promotions</li>
            </ul>
            
            <p>This information may include:</p>
            <ul>
                <li>Name and contact information (email, phone number, address)</li>
                <li>Payment information</li>
                <li>Booking preferences and requirements</li>
                <li>Emergency contact information</li>
                <li>Special dietary or accessibility requirements</li>
            </ul>
            
            <h3>Automatically Collected Information</h3>
            <p>When you visit our website, we automatically collect certain information about your device, including:</p>
            <ul>
                <li>IP address</li>
                <li>Browser type and version</li>
                <li>Operating system</li>
                <li>Pages visited and time spent on each page</li>
                <li>Referring website</li>
                <li>Device information</li>
            </ul>
            
            <h2>How We Use Your Information</h2>
            <p>We use the information we collect to:</p>
            <ul>
                <li>Process and manage your bookings and reservations</li>
                <li>Provide customer support and respond to inquiries</li>
                <li>Send booking confirmations and updates</li>
                <li>Improve our services and website functionality</li>
                <li>Send promotional offers and newsletters (with your consent)</li>
                <li>Ensure the safety and security of our guests</li>
                <li>Comply with legal obligations</li>
            </ul>
            
            <h2>Information Sharing and Disclosure</h2>
            <p>We do not sell, trade, or otherwise transfer your personal information to third parties without your consent, except in the following circumstances:</p>
            <ul>
                <li>To trusted service providers who assist us in operating our website and providing services</li>
                <li>To comply with legal requirements or protect our rights and safety</li>
                <li>In connection with a business transfer or merger</li>
                <li>With your explicit consent</li>
            </ul>
            
            <h2>Data Security</h2>
            <p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:</p>
            <ul>
                <li>Encryption of sensitive data</li>
                <li>Regular security assessments</li>
                <li>Access controls and authentication</li>
                <li>Secure data storage practices</li>
            </ul>
            
            <h2>Your Rights and Choices</h2>
            <p>You have the right to:</p>
            <ul>
                <li>Access and review your personal information</li>
                <li>Update or correct inaccurate information</li>
                <li>Request deletion of your personal information</li>
                <li>Opt-out of marketing communications</li>
                <li>Withdraw consent for data processing</li>
            </ul>
            
            <h2>Cookies and Tracking Technologies</h2>
            <p>We use cookies and similar tracking technologies to enhance your browsing experience and analyze website traffic. You can control cookie settings through your browser preferences.</p>
            
            <h2>Third-Party Links</h2>
            <p>Our website may contain links to third-party websites. We are not responsible for the privacy practices or content of these external sites. We encourage you to review their privacy policies.</p>
            
            <h2>Children's Privacy</h2>
            <p>Our services are not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13. If you believe we have collected such information, please contact us immediately.</p>
            
            <h2>Changes to This Privacy Policy</h2>
            <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date.</p>
            
            <div class="contact-info">
                <h3>Contact Us</h3>
                <p><strong>Adventour Adventure Bandarban</strong></p>
                <p><strong>Email:</strong> privacy@adventour.com</p>
                <p><strong>Phone:</strong> +880 1234-567890</p>
                <p><strong>Address:</strong> Bandarban, Chittagong Division, Bangladesh</p>
                <p>If you have any questions about this Privacy Policy or our data practices, please contact us using the information above.</p>
            </div>
        </div>
        
        <div class="back-link">
            <a href="{{ url()->previous() }}">← Back to Previous Page</a>
        </div>
    </div>
</div>
@endsection
