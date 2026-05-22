<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ filemtime(public_path('css/home.css')) }}">
</head>
<body>
<section class="hero">
    <div id="hero-bg-a" class="hero-bg-layer active"></div>
    <div id="hero-bg-b" class="hero-bg-layer"></div>
    <div class="hero-overlay"></div>

        <header class="hero-top-bar">
            @if (file_exists(public_path('images/la-cucina-logo.png')))
                <a href="{{ url('/') }}" class="site-logo site-logo--image" aria-label="La Cucina Italian Restaurant">
                    <img
                        class="site-logo__img"
                        src="{{ asset('images/la-cucina-logo.png') }}"
                        alt="La Cucina Italian Restaurant"
                        width="200"
                        height="96"
                        loading="eager"
                        decoding="async"
                    >
                </a>
            @else
                <a href="{{ url('/') }}" class="site-logo site-logo--text">
                    <span class="site-logo__title">La Cucina</span>
                    <span class="site-logo__tag">Italian Restaurant</span>
                </a>
            @endif
            <div class="lang-switch"><button class="active" type="button">AR</button><button type="button">EN</button></div>
        </header>

    <div class="hero-content">
        @php
            $homeH1Ar = \App\Models\SiteSetting::getValue('home_h1_text', 'احجز طاولتك بسهولة');
            $homeH1En = \App\Models\SiteSetting::getValue('home_h1_text_en', 'Book your table easily');
            $homeH1Color = \App\Models\SiteSetting::getValue('home_h1_color', '#5b4a36');
        @endphp
        <h1
            id="hero-main-heading"
            style="color: {{ $homeH1Color }};"
            data-h1-ar="{{ e($homeH1Ar) }}"
            data-h1-en="{{ e($homeH1En) }}"
        >{{ $homeH1Ar }}</h1>

        <div class="booking-flow-stack">
        <div class="stepper" id="booking-stepper" role="list" aria-label="خطوات الحجز">
            <span id="pill-1" class="step-pill active" role="listitem">
                <span class="step-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M11 4a7 7 0 105.2 12l3.1 3.1" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="11" cy="11" r="5.2" stroke="currentColor" stroke-width="2"/></svg>
                </span>
                <span id="pill-label-1">البحث</span>
            </span>
            <span id="pill-2" class="step-pill" role="listitem">
                <span class="step-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M7 3h10a2 2 0 012 2v16l-3-2-3 2-3-2-3 2V5a2 2 0 012-2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M9 7h6M9 11h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </span>
                <span id="pill-label-2">تقديم الطلب</span>
            </span>
            <span id="pill-3" class="step-pill" role="listitem">
                <span class="step-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M20 12a8 8 0 01-8 8H6l1.2-3.2A8 8 0 1120 12z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M8.5 12.2c.5 1 1.8 2.3 3.6 3.1 1.2.5 2.4.7 3.3.6.6-.1 1-.5 1.2-1l.3-.9-2-.9-.6.7c-.2.2-.5.3-.8.2a8 8 0 01-2.3-1.5c-.2-.2-.3-.6-.1-.8l.6-.7-1.2-1.8-1 .3c-.5.1-.8.5-.9 1.1-.1.5.1 1.2.4 1.6z" fill="currentColor" opacity=".28"/></svg>
                </span>
                <span id="pill-label-3">إرسال واتساب</span>
            </span>
        </div>
        

        <form
            id="booking-flow"
            class="booking-form"
            data-max-guests="{{ (int) \App\Models\SiteSetting::getValue('max_guest_count', 20) }}"
            data-min-date="{{ now()->toDateString() }}"
            data-booking-whatsapp-phone="{{ \App\Models\SiteSetting::bookingWhatsAppPhoneDigits() }}"
            data-booking-whatsapp-url="{{ \App\Models\SiteSetting::bookingWhatsAppUrl() }}"
        >
            <div id="step-1">
                <div class="form-section-title centered" id="step-1-title"> اختيار الموعد</div>
                <div class="grid grid-2">
                    <div class="field">
                        <label for="reservation_date" id="label-reservation-date">تاريخ الحجز</label>
                        <div class="booking-native-input-wrap" data-native-input="reservation_date">
                            <input id="reservation_date" class="booking-date-input" type="date" min="{{ now()->toDateString() }}" required>
                            <span class="booking-native-input-hint" id="hint-reservation-date" aria-hidden="true">اختر التاريخ</span>
                        </div>
                    </div>
                    <div class="field">
                        <label for="guest_count" id="label-guest-count">عدد الأفراد</label>
                        <div class="counter-control">
                            <button id="guest-plus" class="counter-btn" type="button">+</button>
                            <div>
                                <span id="guest-count-display" class="counter-value">1</span>
                                {{-- <span class="counter-caption">أفراد</span> --}}
                            </div>
                            <button id="guest-minus" class="counter-btn" type="button">-</button>
                        </div>
                        <input id="guest_count" type="hidden" value="1" min="1" max="{{ (int) \App\Models\SiteSetting::getValue('max_guest_count', 20) }}" required>
                    </div>
                </div>
                <div id="availability" class="availability hidden">
                    <p id="slot-selection-error" class="slot-selection-error hidden" role="alert"></p>
                    <div class="availability-picker">
                        <p class="availability-date-line" id="availability-date-line" aria-live="polite"></p>
                        <div class="slot-acc">
                            <button type="button" class="slot-acc-trigger" id="slot-acc-available-trigger" aria-expanded="true" aria-controls="slot-panel-available">
                                <span class="slot-acc-label" id="slot-acc-available-label">المواعيد المتاحة</span>
                                <span class="slot-acc-chevron" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                            </button>
                            <div id="slot-panel-available" class="slot-acc-panel">
                                <div id="slot-grid-available" class="slot-grid-cards"></div>
                                <p id="slot-available-empty" class="slot-empty-msg hidden" role="status"></p>
                            </div>
                        </div>
                        <div class="slot-acc">
                            <button type="button" class="slot-acc-trigger" id="slot-acc-booked-trigger" aria-expanded="true" aria-controls="slot-panel-booked">
                                <span class="slot-acc-label" id="slot-acc-booked-label">المواعيد المحجوزة</span>
                                <span class="slot-acc-chevron" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                            </button>
                            <div id="slot-panel-booked" class="slot-acc-panel">
                                <div id="slot-grid-booked" class="slot-grid-booked-row"></div>
                                <p id="slot-booked-empty" class="slot-empty-msg hidden" role="status"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="actions actions-centered"><button id="search-slots" class="btn" type="button">الخطوة التالية</button></div>
            </div>

            <div id="step-2" class="hidden">
                <div class="form-section-title" id="step-2-title"> الإضافات والطلبات</div>
                <div class="addons-list" id="addons-list"></div>
                <div class="summary-box"><div class="summary-line"><span id="addons-summary-label">ملخص الإضافات</span><span id="addons-summary">لا توجد إضافات</span></div><div class="summary-line"><span id="addons-total-label">إجمالي الإضافات</span><strong id="addons-total">0 SAR</strong></div></div>
                <div class="actions actions-step-footer"><button id="back-to-1" class="btn btn-secondary" type="button">رجوع</button><button id="skip-step-2" class="btn btn-secondary" type="button">تخطي</button><button id="to-step-3" class="btn" type="button">متابعة</button></div>
            </div>

            <div id="step-3" class="hidden">
                <div id="step3-reading-root" class="step3-reading-root">
                <div class="form-section-title step3-title" id="step-3-title">البيانات الشخصية وتأكيد الطلب</div>
                <div class="grid grid-3">
                    <div class="field"><label for="customer_name" id="label-customer-name">الاسم الكامل</label><input id="customer_name" type="text" required autocomplete="name"></div>
                    <div class="field"><label for="customer_phone" id="label-customer-phone">رقم الجوال</label><input id="customer_phone" type="tel" required autocomplete="tel"></div>
                    <div class="field"><label for="customer_email" id="label-customer-email">البريد الإلكتروني</label><input id="customer_email" type="email" autocomplete="email"></div>
                </div>
                <div class="dietary-card dietary-card--modern field">
                    <label id="allergy-main-label" class="visually-hidden">القيود الغذائية</label>
                    <div class="dietary-modern-layout">
                        <div class="dietary-modern-icon-wrap" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M3 13.7V6.3c0-.5.25-.93.62-1.16L11.62 2.06a1 1 0 011-.01l9 6.06c.41.29.62.71.62 1.16v11.73a2 2 0 01-3.14 1.65l-8.5-8.52-7.62 9.62A2 2 0 013 20.93V13.7z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                <circle cx="8.2" cy="8.75" r="1.05" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="dietary-modern-main">
                            <h3 id="diet-display-title" class="dietary-modern-title">القيود الغذائية</h3>
                            <p id="diet-hint" class="dietary-modern-sub">حدد ما يناسبك وضيوفك ثم اضغط حفظ الاختيار.</p>
                            <span id="diet-summary-inline" class="diet-summary-inline" aria-live="polite"></span>
                        </div>
                        <div class="dietary-modern-cta">
                            <button id="diet-add-btn" class="btn-diet-add" type="button"><span id="diet-add-label">+ إضافة</span></button>
                        </div>
                    </div>
                </div>
                <div class="notes-card field">
                    <div class="notes-card-inner">
                        <div class="notes-card-icon-wrap" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <rect x="5" y="3.5" width="14" height="17" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 9.5h8M8 13h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="notes-card-copy">
                            <h3 id="notes-card-title" class="notes-card-title">ملاحظات إضافية</h3>
                            <p id="notes-card-hint" class="notes-card-sub">طلبات خاصة، وقت الوصول، أو أي تفصيل يهم المطعم.</p>
                            <div id="notes-preview" class="notes-preview" aria-live="polite"></div>
                        </div>
                        <button id="notes-open-btn" class="btn-diet-add" type="button"><span id="notes-open-label">إضافة ملاحظات</span></button>
                    </div>
                </div>
                <textarea id="reservation_notes" hidden></textarea>

                {{-- <div class="step3-agreements" role="group" aria-label="الموافقات">
                    <label class="checkbox"><input id="agree_policy" type="checkbox" required> أوافق على سياسة الحجز والإلغاء</label>
                    <label class="checkbox"><input id="confirm_whatsapp" type="checkbox" checked> إرسال تفاصيل الحجز عبر WhatsApp</label>
                </div> --}}

                <div class="summary-panel">
                    <div class="summary-panel-head">
                        <h3 class="summary-panel-title" id="summary-booking-title">ملخص الحجز والطلب</h3>
                        {{-- <span class="summary-panel-deco" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M7 3v4c0 2.5 2 4.5 4.5 4.5S16 9.5 16 7V3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M6 21h12M8 11h8M9 15h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </span> --}}
                        <button id="text-zoom-toggle" class="btn-text-zoom" type="button" aria-pressed="false" aria-label="">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M7 15V5h8M7 15l7-7.5M10 19h9" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6 19h.01" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                <circle cx="6.5" cy="19.5" r="1.7" stroke="currentColor" stroke-width="1.35" fill="none"/>
                            </svg>
                            <span id="text-zoom-label"></span>
                        </button>
                    </div>
                    <div id="final-summary" class="summary-box"></div>
                </div>
                <div class="step3-actions-stack">
                <p id="booking-submit-error" class="booking-submit-error hidden" role="alert" aria-live="assertive"></p>
                <div class="actions actions-centered step3-submit-wrap">
                    <button id="confirm-order-submit" class="btn btn-confirm-wa" type="submit">
                        <span id="confirm-order-label">تأكيد الطلب</span>
                        {{-- <svg class="wa-icon" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg> --}}
                    </button>
                </div>
                <div class="actions actions-centered step3-back-wrap">
                    <button id="back-to-2" class="btn btn-secondary btn-back-step" type="button">
                        <svg class="btn-back-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span id="back-to-2-label">رجوع</span>
                    </button>
                </div>
                </div>
                </div>
            </div>
        </form>
        </div>

        <div id="diet-modal" class="diet-modal hidden" aria-hidden="true">
            <div class="diet-modal-backdrop" id="diet-modal-backdrop" aria-hidden="true"></div>
            <div class="diet-modal-sheet" role="dialog" aria-modal="true" aria-labelledby="diet-modal-tabs">
                <div class="diet-modal-header">
                    <span></span>
                </div>
                <div id="diet-modal-tabs" class="diet-modal-tabs" role="tablist">
                    <button type="button" role="tab" id="diet-tab-self" class="diet-modal-tab active" aria-selected="true"></button>
                    <button type="button" role="tab" id="diet-tab-guests" class="diet-modal-tab" aria-selected="false"></button>
                </div>
                <div class="diet-modal-body">
                    <div id="diet-pane-self" class="diet-pane" role="tabpanel">
                        <div id="allergy-chips-self" class="allergy-chip-grid"></div>
                    </div>
                    <div id="diet-pane-guests" class="diet-pane hidden" role="tabpanel">
                        <div id="allergy-chips-guests" class="allergy-chip-grid"></div>
                    </div>
                </div>
                <div class="diet-modal-footer">
                    <div class="diet-modal-footer-actions">
                        <button type="button" id="diet-cancel" class="btn-diet-footer btn-diet-footer--secondary"></button>
                        <button type="button" id="diet-save" class="btn-diet-footer btn-diet-footer--primary"></button>
                    </div>
                </div>
            </div>
        </div>

        <div id="notes-modal" class="diet-modal notes-modal hidden" aria-hidden="true">
            <div class="diet-modal-backdrop" id="notes-modal-backdrop"></div>
            <div class="diet-modal-sheet notes-modal-sheet" role="dialog" aria-modal="true" aria-labelledby="notes-modal-heading">
                <div class="notes-modal-body">
                    <label id="notes-modal-heading" for="notes-modal-textarea"></label>
                    <textarea id="notes-modal-textarea"></textarea>
                </div>
                <div class="diet-modal-footer">
                    <div class="diet-modal-footer-actions">
                        <button type="button" id="notes-cancel" class="btn-diet-footer btn-diet-footer--secondary"></button>
                        <button type="button" id="notes-save" class="btn-diet-footer btn-diet-footer--primary"></button>
                    </div>
                </div>
            </div>
        </div>

  <section class="website-qr-card" aria-labelledby="website-qr-title">
            <div class="website-qr-copy">
                <span class="website-qr-kicker" id="website-qr-kicker">امسح الكود</span>
                <h2 id="website-qr-title">أطلع علي الأضناف والوجبات الان</h2>
                <p id="website-qr-sub">شارك صفحة الوجبات والأضناف مع ضيوفك أو افتحها مباشرة من الجوال.</p>
            </div>
            <div class="website-qr-frame">
                <img
                    src="{{ asset('images/qr-code.png') }}?v={{ file_exists(public_path('images/qr-code.png')) ? filemtime(public_path('images/qr-code.png')) : 0 }}"
                    alt="La Cucina QR code"
                    loading="lazy"
                    decoding="async"
                >
            </div>
        </section>
        <div class="features">
            <div class="feature">
                <span class="feature-ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M4 7h16M6.5 3.5v3M17.5 3.5v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M6 10h12v10H6V10z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M8.5 13.5h3M8.5 16.5h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span>
                <div class="feature-copy"><strong id="feature-1-title">مواعيد مباشرة</strong><span id="feature-1-sub">عرض فوري للأوقات المتاحة</span></div>
            </div>
            <div class="feature">
                <span class="feature-ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M7 11V7a5 5 0 0110 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M6.5 11h11v9h-11v-9z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg></span>
                <div class="feature-copy"><strong id="feature-2-title">حجز مؤقت</strong><span id="feature-2-sub">تثبيت الموعد أثناء استكمال الخطوات</span></div>
            </div>
            <div class="feature">
                <span class="feature-ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M7 6h14M7 12h14M7 18h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 6h.01M3 12h.01M3 18h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg></span>
                <div class="feature-copy"><strong id="feature-3-title">طلبات وإضافات</strong><span id="feature-3-sub">إضافات مرتبطة بالمناسبة</span></div>
            </div>
            <div class="feature">
                <span class="feature-ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M20 12a8 8 0 01-8 8H6l1.2-3.2A8 8 0 1120 12z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M9.2 12.4c.6 1.2 2.1 2.6 4.2 3.4 1.4.5 2.7.7 3.7.6.7-.1 1.2-.6 1.4-1.2l.3-.9-2.2-1-.7.8c-.2.2-.6.3-.9.2a9.6 9.6 0 01-2.5-1.7c-.3-.3-.3-.7-.1-1l.7-.8-1.3-2-1.1.4c-.6.2-1 .7-1.1 1.4-.1.6.1 1.4.6 1.8z" fill="currentColor" opacity=".28"/></svg></span>
                <div class="feature-copy"><strong id="feature-4-title">WhatsApp</strong><span id="feature-4-sub">إرسال تفاصيل الحجز للمطعم</span></div>
            </div>
        </div>

      

        @php
            $bookingWhatsAppUrl = \App\Models\SiteSetting::bookingWhatsAppUrl();
            $socialLinks = [
                'Instagram' => [
                    'url' => \App\Models\SiteSetting::getValue('social_instagram_url', 'https://www.instagram.com/'),
                    'svg' => '<svg viewBox="0 0 24 24" aria-hidden="true"><defs><linearGradient id="socialInstaG" x1="0%" y1="100%" x2="100%" y2="0%"><stop offset="0%" stop-color="#f09433"/><stop offset="50%" stop-color="#e6683c"/><stop offset="100%" stop-color="#bc1888"/></linearGradient></defs><path fill="url(#socialInstaG)" d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zm0 10.162a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 110 2.881 1.44 1.44 0 010-2.881z"/></svg>',
                ],
                'Facebook' => [
                    'url' => \App\Models\SiteSetting::getValue('social_facebook_url', 'https://www.facebook.com/'),
                    'svg' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#1877F2" d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.469h3.047V9.356c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>',
                ],
                'TikTok' => [
                    'url' => \App\Models\SiteSetting::getValue('social_tiktok_url', 'https://www.tiktok.com/'),
                    'svg' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#000" d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.84-.28-1.79-.2-2.54.29-.96.63-1.5 1.76-1.51 2.87-.02 1.27.68 2.46 1.78 3.02 1.01.52 2.27.42 3.22-.17 1.02-.68 1.51-1.96 1.44-3.18-.01-4.61 0-9.23-.02-13.84z"/></svg>',
                ],
                'WhatsApp' => [
                    'url' => $bookingWhatsAppUrl,
                    'svg' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#25D366" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
                ],
            ];
        @endphp
        <div class="social-fab" id="social-fab">
            <ul class="social-fab__links" id="social-fab-links" aria-hidden="true">
                @foreach ($socialLinks as $label => $link)
                    @if (filled($link['url']))
                        <li>
                            <a class="social-fab__link" href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $label }}">
                                {!! $link['svg'] !!}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
            <button type="button" class="social-fab__toggle" id="social-fab-toggle" aria-expanded="false" aria-controls="social-fab-links" aria-label="روابط التواصل الاجتماعي">
                <svg class="social-fab__icon-open" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                <svg class="social-fab__icon-close" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
            </button>
        </div>
    </div>
</section>
<script src="{{ asset('js/welcome.js') }}?v={{ file_exists(public_path('js/welcome.js')) ? filemtime(public_path('js/welcome.js')) : 0 }}" defer></script>
</body>
</html>
