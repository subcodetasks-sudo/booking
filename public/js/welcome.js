const step1 = document.getElementById('step-1');
const step2 = document.getElementById('step-2');
const step3 = document.getElementById('step-3');
const availability = document.getElementById('availability');

const heroLayerA = document.getElementById('hero-bg-a');
const heroLayerB = document.getElementById('hero-bg-b');
const langButtons = document.querySelectorAll('.lang-switch button');
const guestCountInput = document.getElementById('guest_count');
const guestCountDisplay = document.getElementById('guest-count-display');
const guestPlusButton = document.getElementById('guest-plus');
const guestMinusButton = document.getElementById('guest-minus');
const bookingForm = document.getElementById('booking-flow');

const configuredMaxGuests = Number(bookingForm?.dataset?.maxGuests || 20);
const MAX_GUEST_COUNT = Number.isFinite(configuredMaxGuests) && configuredMaxGuests > 0
    ? configuredMaxGuests
    : 20;
const MIN_BOOKING_DATE = bookingForm?.dataset?.minDate || '';
const panelBookingWindow = {
    start: String(bookingForm?.dataset?.bookingStart || '12:00').trim(),
    end: String(bookingForm?.dataset?.bookingEnd || '23:00').trim(),
    active: bookingForm?.dataset?.bookingActive !== '0',
};

/** يُستخدم فقط عند فشل طلب الشبكة؛ القائمة الفعلية من `/reservation-addons` (منتجات نشطة من جدول products). */
const ADDON_FALLBACK_OPTIONS = [
    { id: null, name_ar: 'باقة ورد', name_en: 'Flower Bouquet', price: 150 },
    { id: null, name_ar: 'كيك مناسبة', name_en: 'Occasion Cake', price: 90 },
    { id: null, name_ar: 'تزيين الطاولة', name_en: 'Table Decoration', price: 120 },
];
let RESERVATION_ADDONS = [];

let selectedSlot = '';
let activeBgLayer = 'a';
let currentLang = 'ar';
try {
    const persistedLang = sessionStorage.getItem('booking_ui_lang');
    if (persistedLang === 'en' || persistedLang === 'ar') {
        currentLang = persistedLang;
    }
} catch {
    /* ignore private mode */
}
const availabilityCache = new Map();
const BOOKING_DRAFT_KEY = 'booking_flow_draft_v2';
let isRestoringDraft = false;

const draftSelfIds = new Set();
const draftGuestIds = new Set();
const committedSelfIds = new Set();
const committedGuestIds = new Set();

const heroImages = [
    // restaurant / fine dining photos
    'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=1800&q=80',
    'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1800&q=80',
    'https://images.unsplash.com/photo-1529692236671-f1f6cf9683ba?auto=format&fit=crop&w=1800&q=80',
    'https://images.unsplash.com/photo-1550966871-3ed3cdb5ed0c?auto=format&fit=crop&w=1800&q=80',
    'https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=1800&q=80',
    'https://images.unsplash.com/photo-1528823872057-9c018a7a7553?auto=format&fit=crop&w=1800&q=80',
];

let currentImageIndex = 0;

const activatePill = (n) => {
    const hero = document.querySelector('.hero');
    if (hero) {
        hero.dataset.step = String(n);
    }

    [1, 2, 3].forEach((i) => {
    document.getElementById(`pill-${i}`).classList.toggle('active', i === n);
    });
};

const formatDate = (d) => (d ? d.split('-').reverse().join('/') : '-');

function padTime(n) {
    return String(n).padStart(2, '0');
}

function normalizeSlotTime(value) {
    const match = String(value || '').trim().match(/^(\d{1,2}):(\d{2})/);
    if (!match) {
        return '';
    }

    return `${padTime(Number(match[1]))}:${padTime(Number(match[2]))}`;
}

function selectAvailableSlot(slot) {
    const gridAvail = document.getElementById('slot-grid-available');
    if (!gridAvail || !slot) {
        return false;
    }

    const btn = [...gridAvail.querySelectorAll('button.slot-card-available')]
        .find((item) => item.dataset.slot === slot);
    if (!btn) {
        return false;
    }

    gridAvail.querySelectorAll('button.slot-card-available').forEach((item) => {
        item.classList.remove('active');
    });
    btn.classList.add('active');
    selectedSlot = slot;
    clearSlotSelectionError();

    return true;
}

function setSlotSelectionError(message) {
    const el = document.getElementById('slot-selection-error');
    if (!el) {
        return;
    }
    el.textContent = message || '';
    el.classList.toggle('hidden', !message);
}

function clearSlotSelectionError() {
    setSlotSelectionError('');
}

function syncPanelBookingWindowFromDom() {
    const hint = document.getElementById('booking-hours-hint');
    const startFromHint = normalizeSlotTime(hint?.dataset?.bookingStart);
    const endFromHint = normalizeSlotTime(hint?.dataset?.bookingEnd);
    const startFromForm = normalizeSlotTime(bookingForm?.dataset?.bookingStart);
    const endFromForm = normalizeSlotTime(bookingForm?.dataset?.bookingEnd);

    if (startFromHint) {
        panelBookingWindow.start = startFromHint;
    } else if (startFromForm) {
        panelBookingWindow.start = startFromForm;
    }
    if (endFromHint) {
        panelBookingWindow.end = endFromHint;
    } else if (endFromForm) {
        panelBookingWindow.end = endFromForm;
    }
    if (bookingForm) {
        panelBookingWindow.active = bookingForm.dataset.bookingActive !== '0';
    }
}

function applyBookingWindowToDom() {
    if (bookingForm) {
        bookingForm.dataset.bookingStart = panelBookingWindow.start;
        bookingForm.dataset.bookingEnd = panelBookingWindow.end;
        bookingForm.dataset.bookingActive = panelBookingWindow.active ? '1' : '0';
    }
    const hint = document.getElementById('booking-hours-hint');
    if (hint) {
        hint.dataset.bookingStart = panelBookingWindow.start;
        hint.dataset.bookingEnd = panelBookingWindow.end;
    }
}

function formatBookingHoursLine(start, end) {
    const L = i18n[currentLang];

    return L.bookingHoursLine
        .replace(':start', start || panelBookingWindow.start)
        .replace(':end', end || panelBookingWindow.end);
}

function updateBookingHoursLine(start, end) {
    syncPanelBookingWindowFromDom();
    const el = document.getElementById('availability-hours-line');
    const hint = document.getElementById('booking-hours-hint');
    const lineStart = normalizeSlotTime(start) || panelBookingWindow.start;
    const lineEnd = normalizeSlotTime(end) || panelBookingWindow.end;
    const text = (!lineStart || !lineEnd) ? '' : formatBookingHoursLine(lineStart, lineEnd);

    if (hint) {
        hint.textContent = text;
    }

    if (!el) {
        return;
    }

    if (!text) {
        el.textContent = '';
        el.classList.add('hidden');
        return;
    }

    el.textContent = text;
    el.classList.remove('hidden');
}

function setAvailabilityNotice(message) {
    const el = document.getElementById('availability-notice');
    if (!el) {
        return;
    }

    if (!message) {
        el.textContent = '';
        el.classList.add('hidden');
        return;
    }

    el.textContent = message;
    el.classList.remove('hidden');
}

function syncGlobalBookingState() {
    const searchBtn = document.getElementById('search-slots');
    if (!panelBookingWindow.active) {
        setAvailabilityNotice(i18n[currentLang].bookingInactive);
        searchBtn?.setAttribute('disabled', 'disabled');
        return;
    }

    searchBtn?.removeAttribute('disabled');
    if (availability?.classList.contains('hidden')) {
        setAvailabilityNotice('');
    }
}

function applyBookingWindowFromPayload(payload) {
    if (!payload || typeof payload !== 'object') {
        return;
    }

    const start = normalizeSlotTime(payload.booking_start);
    const end = normalizeSlotTime(payload.booking_end);
    if (start) {
        panelBookingWindow.start = start;
    }
    if (end) {
        panelBookingWindow.end = end;
    }
    if (typeof payload.booking_active === 'boolean') {
        panelBookingWindow.active = payload.booking_active;
    }
    applyBookingWindowToDom();
}

async function loadBookingWindowFromServer() {
    try {
        const res = await fetch('/booking-window', {
            headers: { Accept: 'application/json' },
            cache: 'no-store',
        });
        if (!res.ok) {
            return;
        }
        const payload = await res.json();
        applyBookingWindowFromPayload(payload);
        updateBookingHoursLine();
        syncGlobalBookingState();
    } catch {
        /* keep values rendered from the server */
    }
}

async function refreshStep1Availability(dateStr, preserveSelection = false) {
    if (!availability || !dateStr || isPastReservationDate(dateStr)) {
        return;
    }

    availability.classList.remove('hidden');
    availabilityCache.delete(dateStr);
    await renderSlotGrid(dateStr, preserveSelection);
}

function formatDateForSlotHeader(iso) {
    if (!iso) {
        return '';
    }
    try {
        const d = new Date(`${iso}T12:00:00`);
        return d.toLocaleDateString(currentLang === 'ar' ? 'ar-EG' : 'en-GB', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    } catch {
        return formatDate(iso);
    }
}

async function fetchAvailabilityPayload(dateStr) {
    if (!dateStr) {
        return { slots: [] };
    }
    if (availabilityCache.has(dateStr)) {
        return availabilityCache.get(dateStr);
    }

    try {
        const res = await fetch(`/availability?date=${encodeURIComponent(dateStr)}`, {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) {
            throw new Error(`HTTP ${res.status}`);
        }
        const payload = await res.json();
        const normalized = {
            ...payload,
            slots: Array.isArray(payload.slots) ? payload.slots : [],
        };
        availabilityCache.set(dateStr, normalized);
        applyBookingWindowFromPayload(normalized);
        return normalized;
    } catch {
        return { slots: [] };
    }
}

const SLOT_BOOKING_META_ICON = '<svg class="slot-info-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.35"/><path d="M12 16v-5M12 8h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';

async function renderSlotGrid(dateStr, preserveSelection = false) {
    const gridAvail = document.getElementById('slot-grid-available');
    const gridBook = document.getElementById('slot-grid-booked');
    const emptyAvail = document.getElementById('slot-available-empty');
    const emptyBook = document.getElementById('slot-booked-empty');
    const dateLine = document.getElementById('availability-date-line');
    const picker = document.querySelector('.availability-picker');

    if (!gridAvail || !gridBook) {
        return;
    }

    if (dateLine) {
        dateLine.textContent = dateStr ? formatDateForSlotHeader(dateStr) : '';
    }

    const prev = preserveSelection ? selectedSlot : '';
    const payload = await fetchAvailabilityPayload(dateStr);
    applyBookingWindowFromPayload(payload);
    updateBookingHoursLine(payload.booking_start, payload.booking_end);
    syncGlobalBookingState();

    const blockedDay = !!payload.day_closed || payload.booking_active === false;

    if (payload.day_closed) {
        setAvailabilityNotice(i18n[currentLang].dayClosed);
    } else if (payload.booking_active === false) {
        setAvailabilityNotice(i18n[currentLang].bookingInactive);
    } else if (payload.day_capacity_full) {
        setAvailabilityNotice(i18n[currentLang].dayCapacityFull);
    } else {
        setAvailabilityNotice('');
    }

    if (blockedDay) {
        picker?.classList.add('hidden');
        gridAvail.innerHTML = '';
        gridBook.innerHTML = '';
        emptyAvail?.classList.add('hidden');
        emptyBook?.classList.add('hidden');
        selectedSlot = '';
        return;
    }

    picker?.classList.remove('hidden');

    const slots = payload.slots || [];
    const word = i18n[currentLang].slotBooked;
    const meta = i18n[currentLang].slotBookingKind;
    // const spotsLbl = i18n[currentLang].slotSpotsLeft;

    const availableSlots = slots.filter((s) => !s.is_unavailable);
    const bookedSlots = slots.filter((s) => s.is_unavailable);
    const available = availableSlots.map((s) => s.time);
    const booked = bookedSlots.map((s) => s.time);

    gridAvail.innerHTML = availableSlots.map((s) => {
        const t = s.time;
        const cap = Number(s.capacity || 1);
        const rem = Number.isFinite(Number(s.spots_remaining))
            ? Number(s.spots_remaining)
            : Math.max(cap - Number(s.reserved || 0), 0);
        const spotsHtml = cap > 1 && rem > 0
            ? `<span class="slot-card-spots">${spotsLbl.replace(':n', String(rem))}</span>`
            : '';
        return `<button type="button" class="slot-card-available" data-slot="${t}" aria-label="${t}">`
            + `<span class="slot-card-time">${t}</span>`
            + spotsHtml
            + `<span class="slot-card-meta">${meta}${SLOT_BOOKING_META_ICON}</span>`
            + '</button>';
    }).join('');

    gridBook.innerHTML = booked.map((t) => (
        `<div class="slot-card-booked" data-slot-time="${t}" aria-label="${word} ${t}">`
        + `<span class="slot-card-booked-time">${t}</span>`
        + `<span class="slot-booked-badge">${word}</span>`
        + '</div>'
    )).join('');

    if (emptyAvail) {
        emptyAvail.textContent = i18n[currentLang].slotNoneAvailable;
        emptyAvail.classList.toggle('hidden', available.length > 0);
    }
    if (emptyBook) {
        emptyBook.textContent = i18n[currentLang].slotNoneBookedDisplay;
        emptyBook.classList.toggle('hidden', booked.length > 0);
    }

    selectedSlot = '';
    const unavailableSet = new Set(booked);
    if (preserveSelection && prev && !unavailableSet.has(prev)) {
        selectAvailableSlot(prev);
    }
}

function setupSlotAccordions() {
    if (document.body.dataset.slotAccordionReady === '1') {
        return;
    }
    ['slot-acc-available-trigger', 'slot-acc-booked-trigger'].forEach((id) => {
        document.getElementById(id)?.addEventListener('click', function accClick() {
            const open = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', open ? 'false' : 'true');
        });
    });
    document.body.dataset.slotAccordionReady = '1';
}

let ALLERGY_OPTIONS = [];

async function loadDietaryOptions() {
    try {
        const res = await fetch('/dietary-options', {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) {
            return;
        }
        const payload = await res.json();
        const options = Array.isArray(payload.options) ? payload.options : [];
        const cleaned = options
            .filter((item) => item && item.id && item.ar && item.en)
            .map((item) => ({
                id: String(item.id),
                ar: String(item.ar),
                en: String(item.en),
            }));

        if (cleaned.length > 0) {
            ALLERGY_OPTIONS = cleaned;
        }
    } catch {
        // Keep fallback options when API is unavailable.
    }
}

function renderReservationAddons() {
    const list = document.getElementById('addons-list');
    if (!list) {
        return;
    }
    if (!RESERVATION_ADDONS.length) {
        const emptyMsg = tr('addonsEmptyCatalog');
        list.innerHTML = `<p class="addon-empty muted">${emptyMsg}</p>`;
        collectAddons();
        return;
    }
    const rows = RESERVATION_ADDONS.map((addon) => {
        const name = currentLang === 'ar' ? addon.name_ar : addon.name_en;
        const price = Number(addon.price || 0);
        const id = addon.id != null && addon.id !== '' ? String(addon.id) : '';
        const safeName = String(name).replace(/"/g, '&quot;');
        return `<div class="addon-item" data-addon-id="${id}" data-addon-name="${safeName}" data-addon-price="${price}">
            <div>
                <div class="addon-title">${name}</div>
                <div class="addon-price">${price} SAR</div>
            </div>
            <div class="qty-box">
                <button class="qty-btn minus" type="button">-</button>
                <span class="qty-value">0</span>
                <button class="qty-btn plus" type="button">+</button>
            </div>
        </div>`;
    }).join('');
    list.innerHTML = rows;
    collectAddons();
}

async function loadReservationAddons() {
    try {
        const res = await fetch('/reservation-addons', {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) {
            RESERVATION_ADDONS = [...ADDON_FALLBACK_OPTIONS];
            renderReservationAddons();
            return;
        }
        const payload = await res.json();
        const addons = Array.isArray(payload.addons) ? payload.addons : [];
        const cleaned = addons
            .filter((item) => item && item.name_ar && item.name_en)
            .map((item) => ({
                id: item.id != null ? Number(item.id) : null,
                name_ar: String(item.name_ar),
                name_en: String(item.name_en),
                price: Number(item.price || 0),
            }));
        RESERVATION_ADDONS = cleaned;
    } catch {
        RESERVATION_ADDONS = [...ADDON_FALLBACK_OPTIONS];
    }
    renderReservationAddons();
}

function persistBookingDraft() {
    if (isRestoringDraft) {
        return;
    }

    try {
        const addonQuantities = {};
        document.querySelectorAll('.addon-item').forEach((row) => {
            const qty = Number(row.querySelector('.qty-value')?.textContent || '0');
            const id = row.dataset.addonId || '';
            const name = row.dataset.addonName || '';
            if (id) {
                addonQuantities[`i:${id}`] = qty;
            } else if (name) {
                addonQuantities[name] = qty;
            }
        });

        const draft = {
            step: getCurrentStep(),
            reservation_date: document.getElementById('reservation_date')?.value || '',
            selected_slot: selectedSlot || '',
            guest_count: document.getElementById('guest_count')?.value || '1',
            customer_name: document.getElementById('customer_name')?.value || '',
            customer_phone: document.getElementById('customer_phone')?.value || '',
            customer_email: document.getElementById('customer_email')?.value || '',
            reservation_notes: document.getElementById('reservation_notes')?.value || '',
            committed_self_ids: [...committedSelfIds],
            committed_guest_ids: [...committedGuestIds],
            addons: addonQuantities,
        };

        sessionStorage.setItem(BOOKING_DRAFT_KEY, JSON.stringify(draft));
    } catch {
        // ignore storage failures
    }
}

function clearBookingDraft() {
    try {
        sessionStorage.removeItem(BOOKING_DRAFT_KEY);
    } catch {
        // ignore storage failures
    }
}

function restoreBookingDraft() {
    try {
        const raw = sessionStorage.getItem(BOOKING_DRAFT_KEY);
        if (!raw) return;
        isRestoringDraft = true;
        const draft = JSON.parse(raw);

        const setValue = (id, value) => {
            const el = document.getElementById(id);
            if (el && typeof value === 'string') el.value = value;
        };

        setValue('reservation_date', draft.reservation_date || '');
        syncNativeInputHints();
        setValue('customer_name', draft.customer_name || '');
        setValue('customer_phone', draft.customer_phone || '');
        setValue('customer_email', draft.customer_email || '');
        setValue('reservation_notes', draft.reservation_notes || '');

        if (guestCountInput && guestCountDisplay) {
            syncGuestCount(draft.guest_count || 1);
        }

        selectedSlot = draft.selected_slot || '';
        committedSelfIds.clear();
        committedGuestIds.clear();
        (draft.committed_self_ids || []).forEach((id) => committedSelfIds.add(id));
        (draft.committed_guest_ids || []).forEach((id) => committedGuestIds.add(id));

        const addonMap = draft.addons || {};
        document.querySelectorAll('.addon-item').forEach((row) => {
            const id = row.dataset.addonId || '';
            const name = row.dataset.addonName || '';
            let qty = 0;
            if (id) {
                qty = Number(addonMap[`i:${id}`] ?? addonMap[name] ?? 0);
            } else {
                qty = Number(addonMap[name] ?? 0);
            }
            const qtyEl = row.querySelector('.qty-value');
            if (qtyEl) qtyEl.textContent = String(Math.max(0, qty));
        });

        updateDietaryRowSummary();
        updateNotesPreview();
        collectAddons();
        renderFinalSummary();

        if (availability && draft.reservation_date) {
            availability.classList.remove('hidden');
            renderSlotGrid(draft.reservation_date, true);
        }

        if (draft.step >= 1 && draft.step <= 3) {
            setStepUI(draft.step);
            syncStepInUrl(draft.step);
        }
    } catch {
        // ignore parse/storage failures
    } finally {
        isRestoringDraft = false;
    }
}

const i18n = {
    ar: {
        pill1: 'البحث',
        pill2: 'تقديم الطلب',
        pill3: 'إرسال واتساب',
        nextStep: 'الخطوة التالية',
        heroTitle: 'احجز طاولتك بسهولة',
        step1Title: 'اختيار الموعد',
        step2Title: 'الإضافات والطلبات',
        step3Title: 'البيانات الشخصية وتأكيد الطلب',
        labelReservationDate: 'تاريخ الحجز',
        hintReservationDate: 'اختر التاريخ',
        labelGuestCount: 'عدد الأفراد',
        labelCustomerName: 'الاسم الكامل',
        labelCustomerPhone: 'رقم الجوال',
        labelCustomerEmail: 'البريد الإلكتروني',
        addonsSummaryLabel: 'ملخص الإضافات',
        addonsTotalLabel: 'إجمالي الإضافات',
        addonsNone: 'لا توجد إضافات',
        addonsEmptyCatalog:
            'لا توجد إضافات متاحة حاليًا. يمكن إضافتها من لوحة الإدارة (المنتجات).',
        skipStep: 'تخطي',
        continueBtn: 'متابعة',
        summaryBookingTitle: 'ملخص الحجز والطلب',
        sumDate: 'التاريخ',
        sumTime: 'الوقت',
        sumGuests: 'الأفراد',
        sumName: 'الاسم',
        sumPhone: 'الهاتف',
        sumEmail: 'البريد الإلكتروني',
        sumOccasion: 'المناسبة',
        sumDietary: 'القيود الغذائية',
        sumNotes: 'ملاحظات إضافية',
        feature1Title: 'مواعيد مباشرة',
        feature1Sub: 'عرض فوري للأوقات المتاحة',
        feature2Title: 'حجز مؤقت',
        feature2Sub: 'تثبيت الموعد أثناء استكمال الخطوات',
        feature3Title: 'طلبات وإضافات',
        feature3Sub: 'إضافات مرتبطة بالمناسبة',
        feature4Title: 'WhatsApp',
        feature4Sub: 'إرسال تفاصيل الحجز للمطعم',
        qrKicker: 'امسح الكود',
        qrTitle: 'أطلع علي الأصناف والوجبات الان',
        qrSub: 'شارك صفحة المنيو مع ضيوفك أو افتحها مباشرة من الجوال.',
        stepperAria: 'خطوات الحجز',
        socialFabAria: 'روابط التواصل الاجتماعي',
        allergyMain: 'القيود الغذائية',
        dietHint: 'حدد ما يناسبك وما يفضلون ضيوفك، ثم اضغط حفظ الاختيار.',
        dietAdd: '+ إضافة',
        occasionsTitle: 'مناسبات خاصة',
        occasionsHint: 'اختر نوع المناسبة لتجهيز أفضل، ثم اضغط حفظ أسفل القائمة.',
        occasionsOpen: '+ إضافة',
        occasionsSave: 'حفظ',
        notesCardTitle: 'ملاحظات إضافية',
        notesCardHint: 'اكتب أي طلب خاص أو وقت وصول أو تفاصيل تساعد المطعم في التجهيز.',
        notesAddBtn: 'إضافة ملاحظات',
        notesModalHeading: 'ملاحظات إضافية',
        notesEmptyPreview: 'لم تُضاف ملاحظات بعد.',
        dietCancel: 'إلغاء',
        dietSave: 'حفظ',
        dietTabYours: 'أنا',
        dietTabGuests: 'الضيوف',
        dietSummaryMine: 'أنا',
        dietSummaryGuests: 'الضيوف',
        dietNonePicked: '',
        confirmOrder: 'تأكيد الطلب',
        textZoomAriaOff: 'تكبير النص والملخص',
        textZoomAriaOn: 'إرجاع حجم النص للوضع الافتراضي',
        slotAccordionAvailable: 'المواعيد المتاحة',
        slotAccordionBooked: 'المواعيد المحجوزة',
        slotBookingKind: 'حجز طاولة',
        // slotSpotsLeft: 'متبقي :n طاولة',
        slotBooked: 'موجود',
        bookingHoursLine: 'ساعات الحجز: من :start إلى :end',
        bookingInactive: 'الحجز عبر الموقع متوقف حاليًا. تواصل مع المطعم مباشرة.',
        dayClosed: 'هذا اليوم مغلق للحجز.',
        dayCapacityFull: 'اكتمل عدد الحجوزات المسموح به لهذا اليوم.',
        slotNoneAvailable: 'لا توجد مواعيد متاحة في هذا اليوم بعد.',
        slotNoneBookedDisplay: 'لا توجد مواعيد محجوزة لهذا اليوم.',
        confirmSlotContinue: 'تأكيد الموعد والمتابعة',
        backToStep2: 'رجوع',
        valEnterReservationDate: 'أدخل تاريخ الحجز.',
        valEnterGuestCount: 'أدخل عدد الأفراد.',
        valDateFuture: 'تاريخ الحجز يجب أن يكون اليوم أو تاريخًا مستقبليًا.',
        valPickSlotFromList: 'اختر وقتًا متاحًا أولاً من قائمة المواعيد.',
        valPickSlotFirst: 'اختر وقتًا متاحًا أولاً.',
        valPleaseEnterDate: 'يرجى إدخال التاريخ.',
        valPleaseEnterGuests: 'يرجى إدخال عدد الأفراد.',
        valPleaseEnterName: 'يرجى إدخال الاسم.',
        valPleaseEnterPhone: 'يرجى إدخال رقم الجوال.',
        valAcceptPolicy: 'يجب الموافقة على سياسة الحجز.',
        errorSaveBooking: 'تعذر حفظ الحجز. تحقق من الاتصال أو حاول لاحقًا.',
        errorSessionExpired: 'انتهت صلاحية الجلسة. حدّث الصفحة ثم حاول مرة أخرى.',
        bookingSavedNoWhatsApp: 'تم حفظ الحجز بنجاح.',
        waTitle: '*طلب حجز جديد*',
        waName: 'الاسم',
        waPhone: 'الهاتف',
        waEmail: 'البريد',
        waDate: 'التاريخ',
        waTime: 'الوقت',
        waGuests: 'الأفراد',
        waAddons: 'الإضافات',
        waAddonsNone: 'لا توجد',
        waTotal: 'الإجمالي',
        waNotes: 'ملاحظات',
        allergiesBlockMine: 'قيود أنا',
        allergiesBlockGuests: 'قيود الضيوف',
    },
    en: {
        pill1: 'Search',
        pill2: 'Submit request',
        pill3: 'Submit & WhatsApp',
        nextStep: 'Next step',
        heroTitle: 'Book your table easily',
        step1Title: 'Choose your visit',
        step2Title: 'Extras & requests',
        step3Title: 'Your details & booking confirmation',
        labelReservationDate: 'Reservation date',
        hintReservationDate: 'Select date',
        labelGuestCount: 'Party size',
        labelCustomerName: 'Full name',
        labelCustomerPhone: 'Mobile number',
        labelCustomerEmail: 'Email address',
        addonsSummaryLabel: 'Extras summary',
        addonsTotalLabel: 'Extras total',
        addonsNone: 'No extras selected',
        addonsEmptyCatalog:
            'No extras are available right now. Add products from the admin panel.',
        skipStep: 'Skip',
        continueBtn: 'Continue',
        summaryBookingTitle: 'Booking summary',
        sumDate: 'Date',
        sumTime: 'Time',
        sumGuests: 'Guests',
        sumName: 'Name',
        sumPhone: 'Phone',
        sumEmail: 'Email',
        sumOccasion: 'Occasion',
        sumDietary: 'Dietary restrictions',
        sumNotes: 'Additional notes',
        feature1Title: 'Live availability',
        feature1Sub: 'See open times instantly',
        feature2Title: 'Soft hold',
        feature2Sub: 'Keep your slot while you finish the steps',
        feature3Title: 'Requests & extras',
        feature3Sub: 'Add-ons that match your occasion',
        feature4Title: 'WhatsApp',
        feature4Sub: 'Send booking details to the restaurant',
        qrKicker: 'Scan the code',
        qrTitle: 'Open La Cucina website quickly',
        qrSub: 'Share the booking page with guests or open it directly on mobile.',
        stepperAria: 'Booking steps',
        socialFabAria: 'Social media links',
        allergyMain: 'Dietary restrictions',
        dietHint: 'Pick what applies to you and your guests, then save.',
        dietAdd: '+ Add',
        occasionsTitle: 'Special occasions',
        occasionsHint: 'Pick one occasion, then tap Save below.',
        occasionsOpen: '+ Add',
        occasionsSave: 'Save',
        notesCardTitle: 'Additional notes',
        notesCardHint: 'Table requests, arrival time, or anything the restaurant should know.',
        notesAddBtn: 'Add notes',
        notesModalHeading: 'Additional notes',
        notesEmptyPreview: 'No notes added yet.',
        dietCancel: 'Cancel',
        dietSave: 'Save',
        dietTabYours: 'Yours',
        dietTabGuests: 'Guests',
        dietSummaryMine: 'Yours',
        dietSummaryGuests: 'Guests',
        dietNonePicked: '',
        confirmOrder: 'Confirm order',
        textZoomShort: 'A+',
        textZoomAriaOff: 'Larger text and summary',
        textZoomAriaOn: 'Default text size',
        slotAccordionAvailable: 'Available times',
        slotAccordionBooked: 'Booked times',
        slotBookingKind: 'Table booking',
        // slotSpotsLeft: ':n spots left',
        slotBooked: 'Booked',
        bookingHoursLine: 'Booking hours: :start – :end',
        bookingInactive: 'Online booking is currently closed. Please contact the restaurant.',
        dayClosed: 'This day is closed for booking.',
        dayCapacityFull: 'Maximum reservations for this day have been reached.',
        slotNoneAvailable: 'No open times left for this day.',
        slotNoneBookedDisplay: 'No booked slots to show for this day.',
        confirmSlotContinue: 'Confirm time & continue',
        backToStep2: 'Back',
        valEnterReservationDate: 'Enter your reservation date.',
        valEnterGuestCount: 'Enter the number of guests.',
        valDateFuture: 'Reservation date must be today or in the future.',
        valPickSlotFromList: 'Choose an available time from the list first.',
        valPickSlotFirst: 'Choose an available time first.',
        valPleaseEnterDate: 'Please enter the date.',
        valPleaseEnterGuests: 'Please enter the party size.',
        valPleaseEnterName: 'Please enter your name.',
        valPleaseEnterPhone: 'Please enter your mobile number.',
        valAcceptPolicy: 'Please accept the booking policy.',
        errorSaveBooking: 'Could not save the booking. Check your connection or try again.',
        errorSessionExpired: 'Your session expired. Refresh the page and try again.',
        bookingSavedNoWhatsApp: 'Your booking was saved successfully.',
        waTitle: '*New booking request*',
        waName: 'Name',
        waPhone: 'Phone',
        waEmail: 'Email',
        waDate: 'Date',
        waTime: 'Time',
        waGuests: 'Guests',
        waAddons: 'Extras',
        waAddonsNone: 'None',
        waTotal: 'Total',
        waNotes: 'Notes',
        allergiesBlockMine: 'Yours',
        allergiesBlockGuests: 'Guests',
    },
};

function tr(key) {
    const pack = i18n[currentLang];
    return pack && Object.prototype.hasOwnProperty.call(pack, key) ? pack[key] : '';
}

function clearBookingSubmitError() {
    const el = document.getElementById('booking-submit-error');
    if (!el) {
        return;
    }
    el.textContent = '';
    el.classList.add('hidden');
}

function showBookingSubmitError(message) {
    const el = document.getElementById('booking-submit-error');
    if (!el || !message) {
        return;
    }
    el.textContent = message;
    el.classList.remove('hidden');
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function firstValidationMessageFromErrors(errors) {
    if (!errors || typeof errors !== 'object') {
        return '';
    }
    for (const msgs of Object.values(errors)) {
        if (Array.isArray(msgs) && msgs[0]) {
            return String(msgs[0]);
        }
    }
    return '';
}

function refreshBodyScrollLock() {
    const diet = document.getElementById('diet-modal');
    const notes = document.getElementById('notes-modal');
    const locked = Boolean(
        (diet && !diet.classList.contains('hidden'))
        || (notes && !notes.classList.contains('hidden')),
    );
    document.body.style.overflow = locked ? 'hidden' : '';
}

function updateNotesPreview() {
    const ta = document.getElementById('reservation_notes');
    const el = document.getElementById('notes-preview');
    if (!ta || !el) {
        return;
    }
    const raw = ta.value.trim();
    const emptyMsg = i18n[currentLang].notesEmptyPreview;
    if (!raw) {
        el.textContent = emptyMsg;
        el.classList.remove('has-text');
        return;
    }
    const max = 220;
    el.textContent = raw.length > max ? `${raw.slice(0, max)}…` : raw;
    el.classList.add('has-text');
}

function openNotesModal() {
    const main = document.getElementById('reservation_notes');
    const draft = document.getElementById('notes-modal-textarea');
    const modal = document.getElementById('notes-modal');
    if (!main || !draft || !modal) {
        return;
    }
    draft.value = main.value;
    modal.classList.remove('hidden');
    modal.removeAttribute('aria-hidden');
    refreshBodyScrollLock();
    draft.focus();
}

function closeNotesModal() {
    const modal = document.getElementById('notes-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        refreshBodyScrollLock();
    }
}

function saveNotesModal() {
    const main = document.getElementById('reservation_notes');
    const draft = document.getElementById('notes-modal-textarea');
    if (main && draft) {
        main.value = draft.value;
    }
    updateNotesPreview();
    renderFinalSummary();
    closeNotesModal();
}

function setupNotesModal() {
    if (document.body.dataset.notesModalReady === '1') {
        return;
    }
    document.getElementById('notes-open-btn')?.addEventListener('click', openNotesModal);
    document.getElementById('notes-modal-backdrop')?.addEventListener('click', closeNotesModal);
    document.getElementById('notes-cancel')?.addEventListener('click', closeNotesModal);
    document.getElementById('notes-save')?.addEventListener('click', saveNotesModal);
    document.body.dataset.notesModalReady = '1';
    updateNotesPreview();
}

function updateTextZoomButtonAria() {
    const btn = document.getElementById('text-zoom-toggle');
    const root = document.getElementById('step3-reading-root');
    if (!btn || !root) {
        return;
    }
    const on = root.classList.contains('is-text-large');
    const lang = currentLang;
    btn.setAttribute('aria-label', on ? i18n[lang].textZoomAriaOn : i18n[lang].textZoomAriaOff);
}

function setupTextZoom() {
    const root = document.getElementById('step3-reading-root');
    const btn = document.getElementById('text-zoom-toggle');
    if (!root || !btn || btn.dataset.bound === '1') {
        return;
    }

    const apply = (on) => {
        root.classList.toggle('is-text-large', on);
        btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        try {
            sessionStorage.setItem('booking_text_zoom', on ? '1' : '0');
        } catch {
            /* ignore */
        }
        updateTextZoomButtonAria();
    };

    let initial = false;
    try {
        initial = sessionStorage.getItem('booking_text_zoom') === '1';
    } catch {
        initial = false;
    }
    apply(initial);

    btn.addEventListener('click', () => apply(!root.classList.contains('is-text-large')));
    btn.dataset.bound = '1';
}

function optLabelFromId(id) {
    const opt = ALLERGY_OPTIONS.find((o) => o.id === id);
    return opt ? (currentLang === 'ar' ? opt.ar : opt.en) : '';
}

function labelsFromIdSet(idSet) {
    return [...idSet].map(optLabelFromId).filter(Boolean);
}

function syncModalChipsFromDraft() {
    document.querySelectorAll('#diet-modal .allergy-chip').forEach((btn) => {
        const scope = btn.dataset.scope;
        const draft = scope === 'guests' ? draftGuestIds : draftSelfIds;
        const on = draft.has(btn.dataset.id);
        btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
}

function updateDietChipLabels() {
    document.querySelectorAll('#diet-modal .allergy-chip').forEach((btn) => {
        const opt = ALLERGY_OPTIONS.find((o) => o.id === btn.dataset.id);
        const span = btn.querySelector('.allergy-chip-label');
        if (opt && span) {
            span.textContent = currentLang === 'ar' ? opt.ar : opt.en;
        }
    });
}

function updateDietaryRowSummary() {
    const el = document.getElementById('diet-summary-inline');
    if (!el) {
        return;
    }
    const mine = labelsFromIdSet(committedSelfIds);
    const guests = labelsFromIdSet(committedGuestIds);
    const sep = currentLang === 'ar' ? '، ' : ', ';
    const parts = [];
    if (mine.length) {
        parts.push(`${i18n[currentLang].dietSummaryMine}: ${mine.slice(0, 2).join(sep)}${mine.length > 2 ? '…' : ''}`);
    }
    if (guests.length) {
        parts.push(`${i18n[currentLang].dietSummaryGuests}: ${guests.slice(0, 2).join(sep)}${guests.length > 2 ? '…' : ''}`);
    }
    if (parts.length) {
        el.textContent = parts.join(' · ');
        el.classList.add('has-detail');
    } else {
        el.textContent = '';
        el.classList.remove('has-detail');
    }
}

function setDietModalTab(tab) {
    const isGuests = tab === 'guests';
    const selfTab = document.getElementById('diet-tab-self');
    const guestTab = document.getElementById('diet-tab-guests');
    const selfPane = document.getElementById('diet-pane-self');
    const guestsPane = document.getElementById('diet-pane-guests');
    if (!selfTab || !guestTab || !selfPane || !guestsPane) {
        return;
    }
    selfTab.classList.toggle('active', !isGuests);
    guestTab.classList.toggle('active', isGuests);
    selfTab.setAttribute('aria-selected', (!isGuests).toString());
    guestTab.setAttribute('aria-selected', isGuests.toString());
    selfPane.classList.toggle('hidden', isGuests);
    guestsPane.classList.toggle('hidden', !isGuests);
}

function openDietModal() {
    draftSelfIds.clear();
    draftGuestIds.clear();
    committedSelfIds.forEach((id) => draftSelfIds.add(id));
    committedGuestIds.forEach((id) => draftGuestIds.add(id));
    syncModalChipsFromDraft();
    setDietModalTab('self');

    const modal = document.getElementById('diet-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.removeAttribute('aria-hidden');
        refreshBodyScrollLock();
    }
}

function closeDietModal() {
    const modal = document.getElementById('diet-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        refreshBodyScrollLock();
    }
}

function saveDietModal() {
    committedSelfIds.clear();
    committedGuestIds.clear();
    draftSelfIds.forEach((id) => committedSelfIds.add(id));
    draftGuestIds.forEach((id) => committedGuestIds.add(id));
    updateDietaryRowSummary();
    renderFinalSummary();
    closeDietModal();
}

function buildChipMarkup(scope) {
    const mkLabel = (opt) => (currentLang === 'ar' ? opt.ar : opt.en);
    return ALLERGY_OPTIONS.map((opt) => (
        `<button type="button" class="allergy-chip" aria-pressed="false" data-id="${opt.id}" data-scope="${scope}">`
        + `<span class="allergy-chip-label">${mkLabel(opt)}</span>`
        + '<span class="allergy-chip-dot" aria-hidden="true"></span></button>'
    )).join('');
}

function setupDietModal() {
    const selfBox = document.getElementById('allergy-chips-self');
    const guestBox = document.getElementById('allergy-chips-guests');
    const body = document.querySelector('.diet-modal-body');
    if (!selfBox || !guestBox || !body || body.dataset.ready === '1') {
        return;
    }

    selfBox.innerHTML = buildChipMarkup('self');
    guestBox.innerHTML = buildChipMarkup('guests');

    body.addEventListener('click', (e) => {
        const chip = e.target.closest('.allergy-chip');
        if (!chip || !body.contains(chip)) {
            return;
        }
        const scope = chip.dataset.scope === 'guests' ? 'guests' : 'self';
        const draft = scope === 'guests' ? draftGuestIds : draftSelfIds;
        const id = chip.dataset.id;
        if (draft.has(id)) {
            draft.delete(id);
        } else {
            draft.add(id);
        }
        chip.setAttribute('aria-pressed', draft.has(id) ? 'true' : 'false');
    });

    document.getElementById('diet-add-btn')?.addEventListener('click', openDietModal);
    document.getElementById('diet-modal-backdrop')?.addEventListener('click', closeDietModal);
    document.getElementById('diet-cancel')?.addEventListener('click', closeDietModal);
    document.getElementById('diet-save')?.addEventListener('click', saveDietModal);
    document.getElementById('diet-tab-self')?.addEventListener('click', () => setDietModalTab('self'));
    document.getElementById('diet-tab-guests')?.addEventListener('click', () => setDietModalTab('guests'));

    body.dataset.ready = '1';
}

function getAllergiesForMessage() {
    const sep = currentLang === 'ar' ? '، ' : ', ';
    const mine = labelsFromIdSet(committedSelfIds);
    const theirs = labelsFromIdSet(committedGuestIds);

    let block = '';

    const hasMine = mine.length > 0;
    const hasTheirs = theirs.length > 0;

    const L = i18n[currentLang];
    if (currentLang === 'ar') {
        if (hasMine) {
            block += `${L.allergiesBlockMine}: ${mine.join(sep)}`;
        }
        if (hasTheirs) {
            block += (block ? `\n${L.allergiesBlockGuests}: ` : `${L.allergiesBlockGuests}: `) + theirs.join(sep);
        }
    } else {
        if (hasMine) {
            block += `${L.allergiesBlockMine}: ${mine.join(sep)}`;
        }
        if (hasTheirs) {
            block += (block ? `\n${L.allergiesBlockGuests}: ` : `${L.allergiesBlockGuests}: `) + theirs.join(sep);
        }
    }

    return block || '-';
}

function applyLanguage(lang) {
    currentLang = lang;
    const L = i18n[lang];
    if (!L) {
        return;
    }
    document.documentElement.lang = lang;
    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
    document.body?.classList.toggle('lang-en', lang === 'en');
    document.body?.classList.toggle('lang-ar', lang === 'ar');

    try {
        sessionStorage.setItem('booking_ui_lang', lang);
    } catch {
        /* ignore */
    }

    const heroHeading = document.getElementById('hero-main-heading');
    if (heroHeading) {
        const fromAr = (heroHeading.getAttribute('data-h1-ar') || '').trim();
        const fromEn = (heroHeading.getAttribute('data-h1-en') || '').trim();
        const tAr = fromAr || i18n.ar.heroTitle;
        const tEn = fromEn || i18n.en.heroTitle;
        heroHeading.textContent = lang === 'ar' ? tAr : tEn;
    }

    document.getElementById('pill-label-1')?.replaceChildren(document.createTextNode(L.pill1));
    document.getElementById('pill-label-2')?.replaceChildren(document.createTextNode(L.pill2));
    document.getElementById('pill-label-3')?.replaceChildren(document.createTextNode(L.pill3));
    document.getElementById('search-slots')?.replaceChildren(document.createTextNode(L.nextStep));

    const step1El = document.getElementById('step-1-title') || document.querySelector('#step-1 .form-section-title');
    if (step1El) step1El.textContent = L.step1Title;
    const step2El = document.getElementById('step-2-title');
    if (step2El) step2El.textContent = L.step2Title;
    const step3El = document.getElementById('step-3-title');
    if (step3El) step3El.textContent = L.step3Title;

    const labelDate = document.getElementById('label-reservation-date');
    if (labelDate) labelDate.textContent = L.labelReservationDate;
    const labelGuests = document.getElementById('label-guest-count');
    if (labelGuests) labelGuests.textContent = L.labelGuestCount;
    const hintDate = document.getElementById('hint-reservation-date');
    if (hintDate) hintDate.textContent = L.hintReservationDate;
    syncNativeInputHints();
    const labelName = document.getElementById('label-customer-name');
    if (labelName) labelName.textContent = L.labelCustomerName;
    const labelPhone = document.getElementById('label-customer-phone');
    if (labelPhone) labelPhone.textContent = L.labelCustomerPhone;
    const labelEmail = document.getElementById('label-customer-email');
    if (labelEmail) labelEmail.textContent = L.labelCustomerEmail;

    const addonsSumLbl = document.getElementById('addons-summary-label');
    if (addonsSumLbl) addonsSumLbl.textContent = L.addonsSummaryLabel;
    const addonsTotLbl = document.getElementById('addons-total-label');
    if (addonsTotLbl) addonsTotLbl.textContent = L.addonsTotalLabel;

    const summaryBookingTitleEl = document.getElementById('summary-booking-title');
    if (summaryBookingTitleEl) summaryBookingTitleEl.textContent = L.summaryBookingTitle;

    const f1t = document.getElementById('feature-1-title');
    if (f1t) f1t.textContent = L.feature1Title;
    const f1s = document.getElementById('feature-1-sub');
    if (f1s) f1s.textContent = L.feature1Sub;
    const f2t = document.getElementById('feature-2-title');
    if (f2t) f2t.textContent = L.feature2Title;
    const f2s = document.getElementById('feature-2-sub');
    if (f2s) f2s.textContent = L.feature2Sub;
    const f3t = document.getElementById('feature-3-title');
    if (f3t) f3t.textContent = L.feature3Title;
    const f3s = document.getElementById('feature-3-sub');
    if (f3s) f3s.textContent = L.feature3Sub;
    const f4t = document.getElementById('feature-4-title');
    if (f4t) f4t.textContent = L.feature4Title;
    const f4s = document.getElementById('feature-4-sub');
    if (f4s) f4s.textContent = L.feature4Sub;
    const qrKicker = document.getElementById('website-qr-kicker');
    if (qrKicker) qrKicker.textContent = L.qrKicker;
    const qrTitle = document.getElementById('website-qr-title');
    if (qrTitle) qrTitle.textContent = L.qrTitle;
    const qrSub = document.getElementById('website-qr-sub');
    if (qrSub) qrSub.textContent = L.qrSub;

    document.getElementById('booking-stepper')?.setAttribute('aria-label', L.stepperAria);
    document.getElementById('social-fab-toggle')?.setAttribute('aria-label', L.socialFabAria);

    const backTo1 = document.getElementById('back-to-1');
    if (backTo1) backTo1.textContent = L.backToStep2;
    const toStep3 = document.getElementById('to-step-3');
    if (toStep3) toStep3.textContent = L.continueBtn;

    const allergyMain = document.getElementById('allergy-main-label');
    const dietTitle = document.getElementById('diet-display-title');
    const dietHint = document.getElementById('diet-hint');
    const dietAdd = document.getElementById('diet-add-label');
    const notesTitle = document.getElementById('notes-card-title');
    const notesHint = document.getElementById('notes-card-hint');
    const notesOpen = document.getElementById('notes-open-label');
    const notesModalHeading = document.getElementById('notes-modal-heading');
    if (allergyMain) {
        allergyMain.textContent = L.allergyMain;
    }
    if (dietTitle) {
        dietTitle.textContent = L.allergyMain;
    }
    if (dietHint) {
        dietHint.textContent = L.dietHint;
    }
    if (dietAdd) {
        dietAdd.textContent = L.dietAdd;
    }
    if (notesTitle) {
        notesTitle.textContent = L.notesCardTitle;
    }
    if (notesHint) {
        notesHint.textContent = L.notesCardHint;
    }
    if (notesOpen) {
        notesOpen.textContent = L.notesAddBtn;
    }
    if (notesModalHeading) {
        notesModalHeading.textContent = L.notesModalHeading;
    }
    document.getElementById('diet-cancel')?.replaceChildren(document.createTextNode(L.dietCancel));
    document.getElementById('diet-save')?.replaceChildren(document.createTextNode(L.dietSave));
    document.getElementById('notes-cancel')?.replaceChildren(document.createTextNode(L.dietCancel));
    document.getElementById('notes-save')?.replaceChildren(document.createTextNode(L.dietSave));
    document.getElementById('diet-tab-self')?.replaceChildren(document.createTextNode(L.dietTabYours));
    document.getElementById('diet-tab-guests')?.replaceChildren(document.createTextNode(L.dietTabGuests));
    updateDietChipLabels();
    updateDietaryRowSummary();
    updateNotesPreview();

    const confirmLabel = document.getElementById('confirm-order-label');
    if (confirmLabel) {
        confirmLabel.textContent = L.confirmOrder;
    }
    document.getElementById('slot-acc-available-label')?.replaceChildren(document.createTextNode(L.slotAccordionAvailable));
    document.getElementById('slot-acc-booked-label')?.replaceChildren(document.createTextNode(L.slotAccordionBooked));

    const backTo2Label = document.getElementById('back-to-2-label');
    if (backTo2Label) {
        backTo2Label.textContent = L.backToStep2;
    }
    const skipStep2 = document.getElementById('skip-step-2');
    if (skipStep2) {
        skipStep2.textContent = L.skipStep;
    }
    renderReservationAddons();
    updateBookingHoursLine();
    syncGlobalBookingState();
    if (availability && !availability.classList.contains('hidden')) {
        renderSlotGrid(document.getElementById('reservation_date')?.value || '', true);
    }

    langButtons.forEach((button) => {
        const isActive = button.textContent.trim().toLowerCase() === lang;
        button.classList.toggle('active', isActive);
    });

    updateTextZoomButtonAria();
    if (typeof getCurrentStep === 'function' && getCurrentStep() === 3) {
        renderFinalSummary();
    }
}

function syncNativeInputHints() {
    document.querySelectorAll('.booking-native-input-wrap').forEach((wrap) => {
        const input = wrap.querySelector('input');
        wrap.classList.toggle('is-filled', Boolean(input?.value));
    });
}

function syncGuestCount(value) {
    const sanitized = Math.max(1, Math.min(MAX_GUEST_COUNT, Number(value) || 1));
    guestCountInput.value = String(sanitized);
    guestCountDisplay.textContent = String(sanitized);
}

function isPastReservationDate(value) {
    if (!value || !MIN_BOOKING_DATE) {
        return false;
    }
    return value < MIN_BOOKING_DATE;
}

function setupHeroSlider() {
    if (!heroLayerA || !heroLayerB) {
        return;
    }

    heroLayerA.style.backgroundImage = `url('${heroImages[0]}')`;
    heroLayerA.classList.add('active');

    setInterval(() => {
        currentImageIndex = (currentImageIndex + 1) % heroImages.length;
        const nextImage = heroImages[currentImageIndex];

        if (activeBgLayer === 'a') {
            heroLayerB.style.backgroundImage = `url('${nextImage}')`;
            heroLayerB.classList.add('active');
            heroLayerA.classList.remove('active');
            activeBgLayer = 'b';
        } else {
            heroLayerA.style.backgroundImage = `url('${nextImage}')`;
            heroLayerA.classList.add('active');
            heroLayerB.classList.remove('active');
            activeBgLayer = 'a';
        }
    }, 5500);
}

function collectAddons() {
    let total = 0;
    const items = [];
    const addons = [];

    document.querySelectorAll('.addon-item').forEach((row) => {
        const qty = Number(row.querySelector('.qty-value').textContent || '0');
        const price = Number(row.dataset.addonPrice || '0');
        const name = row.dataset.addonName || '';
        const addonId = row.dataset.addonId || '';

        if (qty > 0) {
            items.push(`${name} x${qty}`);
            total += qty * price;
            if (addonId) {
                addons.push({ addon_id: Number(addonId), quantity: qty });
            } else {
                addons.push({ name, price, quantity: qty });
            }
        }
    });

    const listSep = currentLang === 'ar' ? '، ' : ', ';
    document.getElementById('addons-summary').textContent = items.length ? items.join(listSep) : tr('addonsNone');
    document.getElementById('addons-total').textContent = `${total} SAR`;

    return { items, total, addons };
}

function renderFinalSummary() {
    const box = document.getElementById('final-summary');
    if (!box) {
        return;
    }

    const addons = collectAddons();
    const customerName = document.getElementById('customer_name')?.value?.trim() || '-';
    const customerPhone = document.getElementById('customer_phone')?.value?.trim() || '-';
    const customerEmail = document.getElementById('customer_email')?.value?.trim() || '-';
    const allergiesText = getAllergiesForMessage() || '-';
    const notesText = document.getElementById('reservation_notes')?.value?.trim() || '-';

    const L = i18n[currentLang];
    box.innerHTML = `
        <div class="summary-line"><span>${L.sumDate}</span><strong>${formatDate(document.getElementById('reservation_date').value)}</strong></div>
        <div class="summary-line"><span>${L.sumTime}</span><strong>${selectedSlot || '-'}</strong></div>
        <div class="summary-line"><span>${L.sumGuests}</span><strong>${document.getElementById('guest_count').value || '-'}</strong></div>
        <div class="summary-line"><span>${L.sumName}</span><strong>${customerName}</strong></div>
        <div class="summary-line"><span>${L.sumPhone}</span><strong>${customerPhone}</strong></div>
        <div class="summary-line"><span>${L.sumEmail}</span><strong>${customerEmail}</strong></div>
        <div class="summary-line"><span>${L.sumDietary}</span><strong>${allergiesText}</strong></div>
        <div class="summary-line"><span>${L.sumNotes}</span><strong>${notesText}</strong></div>`;

    persistBookingDraft();
}

function buildWhatsAppMessage(name, phone, addons) {
    const L = i18n[currentLang];
    const listSep = currentLang === 'ar' ? '، ' : ', ';
    const email = document.getElementById('customer_email')?.value || '-';
    return [
        L.waTitle,
        `${L.waName}: ${name}`,
        `${L.waPhone}: ${phone}`,
        `${L.waEmail}: ${email}`,
        `${L.waDate}: ${formatDate(document.getElementById('reservation_date')?.value)}`,
        `${L.waTime}: ${selectedSlot}`,
        `${L.waGuests}: ${document.getElementById('guest_count')?.value}`,
        `${L.waAddons}: ${addons.items.length ? addons.items.join(listSep) : L.waAddonsNone}`,
        `${L.waTotal}: ${addons.total} SAR`,
        `${L.allergyMain}: ${getAllergiesForMessage()}`,
        `${L.waNotes}: ${document.getElementById('reservation_notes')?.value || '-'}`,
    ].join('\n');
}

function buildWhatsAppUrl(message) {
    const phone = String(bookingForm?.dataset?.bookingWhatsappPhone || '').replace(/\D+/g, '');
    const baseUrl = phone
        ? `https://wa.me/${phone}`
        : String(bookingForm?.dataset?.bookingWhatsappUrl || '').trim();
    if (!baseUrl) {
        return '#';
    }
    const glue = baseUrl.includes('?') ? '&' : '?';

    return `${baseUrl}${glue}text=${encodeURIComponent(message)}`;
}

function getCurrentStep() {
    const hero = document.querySelector('.hero');
    const step = Number(hero?.dataset?.step || '1');
    return Number.isFinite(step) ? step : 1;
}

function setStepUI(stepNumber) {
    step1.classList.toggle('hidden', stepNumber !== 1);
    step2.classList.toggle('hidden', stepNumber !== 2);
    step3.classList.toggle('hidden', stepNumber !== 3);
    activatePill(stepNumber);
}

function syncStepInUrl(stepNumber) {
    const url = new URL(window.location.href);
    url.searchParams.set('step', String(stepNumber));
    window.history.replaceState({}, '', url.toString());
}

function readStepFromUrl() {
    const url = new URL(window.location.href);
    const fromQuery = Number(url.searchParams.get('step') || '');
    if (fromQuery >= 1 && fromQuery <= 3) {
        return fromQuery;
    }

    const hash = window.location.hash || '';
    const match = hash.match(/step-([123])/);
    if (match) {
        return Number(match[1]);
    }

    return 1;
}

function setFieldError(field, message) {
    if (!field) return;
    field.setCustomValidity(message || '');
    if (message) {
        field.reportValidity();
        field.focus();
    }
}

function clearFieldError(field) {
    if (!field) return;
    field.setCustomValidity('');
}

function canMoveToStep(targetStep) {
    const currentStep = getCurrentStep();

    // Moving back is always allowed.
    if (targetStep <= currentStep) {
        return true;
    }

    // To reach step 2/3, step 1 must be completed first.
    const dateInput = document.getElementById('reservation_date');
    const guestInput = document.getElementById('guest_count');
    const d = dateInput?.value;
    const g = guestInput?.value;
    if (!d || !g) {
        const V = i18n[currentLang];
        setFieldError(dateInput, !d ? V.valEnterReservationDate : '');
        setFieldError(guestInput, !g ? V.valEnterGuestCount : '');
        return false;
    }
    if (isPastReservationDate(d)) {
        setFieldError(dateInput, i18n[currentLang].valDateFuture);
        return false;
    }
    clearFieldError(dateInput);
    clearFieldError(guestInput);

    if (!selectedSlot) {
        setSlotSelectionError(i18n[currentLang].valPickSlotFromList);
        return false;
    }
    clearSlotSelectionError();

    // To reach step 3 directly from stepper, step 2 must be visited/completed.
    if (targetStep >= 3 && step2.classList.contains('hidden') && step3.classList.contains('hidden')) {
        goToStep(2);
        return false;
    }

    return true;
}

function goToStep(stepNumber) {
    if (!canMoveToStep(stepNumber)) {
        return;
    }
    setStepUI(stepNumber);
    if (stepNumber === 3) {
        // Always refresh summary when entering step 3 (buttons or step pills).
        renderFinalSummary();
    }
    syncStepInUrl(stepNumber);
    persistBookingDraft();
}

document.getElementById('search-slots').addEventListener('click', async () => {
    const dateInput = document.getElementById('reservation_date');
    const guestInput = document.getElementById('guest_count');
    const d = dateInput.value;
    const g = guestInput.value;

    const V = i18n[currentLang];
    if (!d || !g) {
        setFieldError(dateInput, !d ? V.valPleaseEnterDate : '');
        setFieldError(guestInput, !g ? V.valPleaseEnterGuests : '');
        return;
    }
    if (isPastReservationDate(d)) {
        setFieldError(dateInput, V.valDateFuture);
        return;
    }
    clearFieldError(dateInput);
    clearFieldError(guestInput);

    await refreshStep1Availability(d, true);

    if (!selectedSlot) {
        setSlotSelectionError(V.valPickSlotFirst);
        requestAnimationFrame(() => {
            availability?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
        persistBookingDraft();
        return;
    }

    clearSlotSelectionError();
    goToStep(2);
    persistBookingDraft();
});

document.getElementById('slot-grid-available')?.addEventListener('click', (e) => {
    const btn = e.target.closest('button.slot-card-available');
    if (!btn) {
        return;
    }
    selectAvailableSlot(btn.dataset.slot || '');
    persistBookingDraft();
});

document.getElementById('reservation_date')?.addEventListener('input', syncNativeInputHints);
document.getElementById('reservation_date')?.addEventListener('change', async () => {
    const dateInput = document.getElementById('reservation_date');
    const dateValue = dateInput?.value || '';
    syncNativeInputHints();

    if (isPastReservationDate(dateValue)) {
        setFieldError(dateInput, i18n[currentLang].valDateFuture);
        availability?.classList.add('hidden');
        selectedSlot = '';
        clearSlotSelectionError();
        persistBookingDraft();
        return;
    }

    clearFieldError(dateInput);
    if (!dateValue) {
        availability?.classList.add('hidden');
        selectedSlot = '';
        clearSlotSelectionError();
        persistBookingDraft();
        return;
    }

    selectedSlot = '';
    clearSlotSelectionError();
    await refreshStep1Availability(dateValue, false);
    persistBookingDraft();
});

document.getElementById('back-to-1').addEventListener('click', () => {
    goToStep(1);
});

document.getElementById('back-to-2').addEventListener('click', () => {
    goToStep(2);
});

document.getElementById('addons-list')?.addEventListener('click', (e) => {
    const plusBtn = e.target.closest('.plus');
    const minusBtn = e.target.closest('.minus');
    if (!plusBtn && !minusBtn) {
        return;
    }
    const qtyEl = e.target.closest('.qty-box')?.querySelector('.qty-value');
    if (!qtyEl) {
        return;
    }
    const current = Number(qtyEl.textContent || '0');
    qtyEl.textContent = String(plusBtn ? current + 1 : Math.max(0, current - 1));
    collectAddons();
    persistBookingDraft();
});

document.getElementById('to-step-3').addEventListener('click', () => {
    renderFinalSummary();
    goToStep(3);
});
document.getElementById('skip-step-2')?.addEventListener('click', () => {
    document.querySelectorAll('#addons-list .qty-value').forEach((el) => {
        el.textContent = '0';
    });
    collectAddons();
    renderFinalSummary();
    goToStep(3);
});

['customer_name', 'customer_phone', 'customer_email', 'reservation_notes'].forEach((id) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', () => {
        clearBookingSubmitError();
        renderFinalSummary();
    });
    el.addEventListener('change', () => {
        clearBookingSubmitError();
        renderFinalSummary();
    });
});

['reservation_time', 'guest_count'].forEach((id) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('input', persistBookingDraft);
    el.addEventListener('change', persistBookingDraft);
});

['pill-1', 'pill-2', 'pill-3'].forEach((id, index) => {
    const pill = document.getElementById(id);
    if (!pill) {
        return;
    }

    pill.style.cursor = 'pointer';
    pill.addEventListener('click', () => goToStep(index + 1));
});

// Keep current step on refresh via URL (?step=1|2|3).
const initialStep = readStepFromUrl();
setStepUI(initialStep);
syncStepInUrl(initialStep);
if (initialStep === 3) {
    renderFinalSummary();
}

document.getElementById('booking-flow').addEventListener('submit', (e) => {
    e.preventDefault();

    const name = document.getElementById('customer_name').value.trim();
    const phone = document.getElementById('customer_phone').value.trim();

    const nameInput = document.getElementById('customer_name');
    const phoneInput = document.getElementById('customer_phone');
    const Vsub = i18n[currentLang];
    if (!name || !phone) {
        setFieldError(nameInput, !name ? Vsub.valPleaseEnterName : '');
        setFieldError(phoneInput, !phone ? Vsub.valPleaseEnterPhone : '');
        return;
    }
    clearFieldError(nameInput);
    clearFieldError(phoneInput);

    const agreePolicy = document.getElementById('agree_policy');
    if (agreePolicy && !agreePolicy.checked) {
        setFieldError(agreePolicy, i18n[currentLang].valAcceptPolicy);
        return;
    }
    clearFieldError(agreePolicy);

    const timeInputForSlot = document.getElementById('reservation_time');
    if (!selectedSlot) {
        setFieldError(timeInputForSlot, i18n[currentLang].valPickSlotFromList);
        return;
    }
    clearFieldError(timeInputForSlot);

    const addons = collectAddons();

    const msg = buildWhatsAppMessage(name, phone, addons);

    clearBookingSubmitError();

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const payload = {
        reservation_date: document.getElementById('reservation_date').value,
        reservation_time: selectedSlot,
        guest_count: Number(document.getElementById('guest_count').value || 0),
        customer_name: name,
        customer_phone: phone,
        customer_email: document.getElementById('customer_email').value || null,
        occasion_id: null,
        occasion: null,
        dietary_self_ids: [...committedSelfIds],
        dietary_guest_ids: [...committedGuestIds],
        allergies_notes: getAllergiesForMessage(),
        reservation_notes: document.getElementById('reservation_notes').value || null,
        addons: addons.addons,
    };

    fetch('/reservations', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
        },
        body: JSON.stringify(payload),
    })
        .then(async (res) => {
            const contentType = res.headers.get('content-type') || '';
            let json = {};
            if (contentType.includes('application/json')) {
                try {
                    json = await res.json();
                } catch {
                    json = {};
                }
            } else {
                await res.text();
            }
            if (!res.ok) {
                const err = new Error(json.message || `Request failed: ${res.status}`);
                err.status = res.status;
                err.body = json;
                throw err;
            }
            return json;
        })
        .then(() => {
            clearBookingSubmitError();
            clearBookingDraft();
            const sendWhatsApp = document.getElementById('confirm_whatsapp')?.checked !== false;
            if (sendWhatsApp) {
                window.location.href = buildWhatsAppUrl(msg);
                return;
            }
            goToStep(1);
            showBookingSubmitError(i18n[currentLang].bookingSavedNoWhatsApp);
        })
        .catch((err) => {
            const json = err.body && typeof err.body === 'object' ? err.body : {};
            clearFieldError(phoneInput);

            const validationFieldMap = {
                customer_phone: 'customer_phone',
                customer_name: 'customer_name',
                reservation_date: 'reservation_date',
                reservation_time: 'reservation_time',
                guest_count: 'guest_count',
                customer_email: 'customer_email',
            };
            if (err.status === 422 && json.errors && typeof json.errors === 'object') {
                for (const [, domId] of Object.entries(validationFieldMap)) {
                    clearFieldError(document.getElementById(domId));
                }
                for (const [apiKey, domId] of Object.entries(validationFieldMap)) {
                    const first = json.errors[apiKey]?.[0];
                    if (first) {
                        const el = document.getElementById(domId);
                        if (el) {
                            setFieldError(el, first);
                            return;
                        }
                    }
                }
                const orphan = firstValidationMessageFromErrors(json.errors);
                if (orphan) {
                    showBookingSubmitError(orphan);
                    return;
                }
            }

            if (err.status === 419) {
                showBookingSubmitError(i18n[currentLang].errorSessionExpired);
                return;
            }

            const serverMsg = typeof json.message === 'string' && json.message.trim()
                ? json.message.trim()
                : '';
            showBookingSubmitError(serverMsg || i18n[currentLang].errorSaveBooking);
        });
});

setupHeroSlider();

if (guestPlusButton && guestMinusButton && guestCountInput && guestCountDisplay) {
    guestCountInput.max = String(MAX_GUEST_COUNT);
    syncGuestCount(guestCountInput.value || 1);

    guestPlusButton.addEventListener('click', () => {
        syncGuestCount(Number(guestCountInput.value) + 1);
        persistBookingDraft();
    });

    guestMinusButton.addEventListener('click', () => {
        syncGuestCount(Number(guestCountInput.value) - 1);
        persistBookingDraft();
    });
}

const reservationDateInput = document.getElementById('reservation_date');
if (reservationDateInput && MIN_BOOKING_DATE) {
    reservationDateInput.min = MIN_BOOKING_DATE;
}

langButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const lang = button.textContent.trim().toLowerCase() === 'en' ? 'en' : 'ar';
        applyLanguage(lang);
    });
});

/** Apply saved UI language before async loaders finish so headings/labels match pills immediately. */
syncPanelBookingWindowFromDom();
applyLanguage(currentLang);
updateBookingHoursLine();
syncGlobalBookingState();
syncNativeInputHints();
loadBookingWindowFromServer();

function setupSocialFab() {
    const root = document.getElementById('social-fab');
    const toggle = document.getElementById('social-fab-toggle');
    const links = document.getElementById('social-fab-links');
    if (!root || !toggle || !links || toggle.dataset.bound === '1') {
        return;
    }

    const setOpen = (open) => {
        root.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        links.setAttribute('aria-hidden', open ? 'false' : 'true');
    };

    toggle.addEventListener('click', () => {
        setOpen(!root.classList.contains('is-open'));
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && root.classList.contains('is-open')) {
            setOpen(false);
        }
    });

    document.addEventListener('click', (e) => {
        if (!root.classList.contains('is-open')) {
            return;
        }
        if (!root.contains(e.target)) {
            setOpen(false);
        }
    });

    toggle.dataset.bound = '1';
}

Promise.all([loadDietaryOptions(), loadReservationAddons()]).finally(() => {
    try {
        const savedLang = sessionStorage.getItem('booking_ui_lang');
        if (savedLang === 'en' || savedLang === 'ar') {
            currentLang = savedLang;
            langButtons.forEach((button) => {
                const isActive = button.textContent.trim().toLowerCase() === savedLang;
                button.classList.toggle('active', isActive);
            });
        }
    } catch {
        /* ignore */
    }

    setupDietModal();
    setupSlotAccordions();
    applyLanguage(currentLang);
    updateBookingHoursLine();
    syncGlobalBookingState();
    setupNotesModal();
    setupTextZoom();
    setupSocialFab();
    restoreBookingDraft();
});

window.addEventListener('beforeunload', () => {
    persistBookingDraft();
});

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState !== 'visible') {
        return;
    }
    loadBookingWindowFromServer();
    availabilityCache.clear();
    const dateStr = document.getElementById('reservation_date')?.value?.trim();
    if (dateStr && availability && !availability.classList.contains('hidden') && !isPastReservationDate(dateStr)) {
        renderSlotGrid(dateStr, true);
    }
});
