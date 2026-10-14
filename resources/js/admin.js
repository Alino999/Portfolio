const themeSelect = document.getElementById('admin-theme-select');
const sidebar = document.getElementById('admin-sidebar');
const sidebarToggle = document.getElementById('admin-sidebar-toggle');
const sidebarClose = document.getElementById('admin-sidebar-close');
const adminShell = document.getElementById('admin-shell');
const themeStorageKey = 'portfolio-admin-theme';
const sidebarStorageKey = 'portfolio-admin-sidebar-collapsed';
const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
const desktopQuery = window.matchMedia('(min-width: 992px)');

const readPreference = (key, fallback) => {
    try {
        return window.localStorage.getItem(key) ?? fallback;
    } catch {
        return fallback;
    }
};

const savePreference = (key, value) => {
    try {
        window.localStorage.setItem(key, value);
    } catch {
    }
};

let themePreference = readPreference(themeStorageKey, 'auto');

const applyTheme = () => {
    const theme = themePreference === 'auto'
        ? (darkModeQuery.matches ? 'dark' : 'light')
        : themePreference;

    document.documentElement.setAttribute('data-bs-theme', theme);
};

if (themeSelect) {
    themeSelect.value = themePreference;
    applyTheme();
    themeSelect.addEventListener('change', () => {
        themePreference = themeSelect.value;
        savePreference(themeStorageKey, themePreference);
        applyTheme();
    });
}

darkModeQuery.addEventListener('change', () => {
    if (themePreference === 'auto') applyTheme();
});

const updateSidebarToggle = () => {
    if (!sidebarToggle) return;

    if (desktopQuery.matches) {
        const expanded = !adminShell?.classList.contains('is-sidebar-collapsed');
        sidebarToggle.setAttribute('aria-expanded', String(expanded));
        sidebarToggle.setAttribute('aria-label', expanded ? 'Réduire le menu' : 'Développer le menu');
        return;
    }

    const expanded = sidebar?.classList.contains('show') ?? false;
    sidebarToggle.setAttribute('aria-expanded', String(expanded));
    sidebarToggle.setAttribute('aria-label', expanded ? 'Fermer le menu' : 'Ouvrir le menu');
};

if (adminShell && readPreference(sidebarStorageKey, 'false') === 'true') {
    adminShell.classList.add('is-sidebar-collapsed');
}

updateSidebarToggle();

desktopQuery.addEventListener('change', updateSidebarToggle);
sidebar?.addEventListener('shown.bs.offcanvas', updateSidebarToggle);
sidebar?.addEventListener('hidden.bs.offcanvas', updateSidebarToggle);

sidebarToggle?.addEventListener('click', () => {
    if (!sidebar || !adminShell) return;

    if (desktopQuery.matches) {
        const collapsed = adminShell.classList.toggle('is-sidebar-collapsed');
        savePreference(sidebarStorageKey, String(collapsed));
        updateSidebarToggle();
        return;
    }

    window.bootstrap?.Offcanvas.getOrCreateInstance(sidebar).toggle();
});

sidebarClose?.addEventListener('click', () => {
    window.bootstrap?.Offcanvas.getInstance(sidebar)?.hide();
});

const bulkActionForms = document.querySelectorAll('[data-bulk-action-form]');
bulkActionForms.forEach((form) => {
    const selection = document.querySelectorAll(`[data-bulk-item][form="${form.id}"]`);
    const selectAll = document.querySelector(`[data-bulk-select-all][form="${form.id}"]`);
    const submitButton = form.querySelector('[data-bulk-submit]');
    const selectedCount = form.querySelector('[data-bulk-count]');
    const updateSelection = () => {
        const selected = [...selection].filter((checkbox) => checkbox.checked).length;
        if (selectedCount) selectedCount.textContent = String(selected);
        if (selectAll) selectAll.checked = selection.length > 0 && selected === selection.length;
        if (submitButton) submitButton.disabled = selected === 0;
    };

    selection.forEach((checkbox) => checkbox.addEventListener('change', updateSelection));
    selectAll?.addEventListener('change', () => {
        selection.forEach((checkbox) => { checkbox.checked = selectAll.checked; });
        updateSelection();
    });
    form.addEventListener('submit', (event) => {
        const action = form.querySelector('[name="action"]')?.value;
        const selected = [...selection].filter((checkbox) => checkbox.checked).length;
        if (action === 'delete' && !window.confirm(`Supprimer ${selected} élément(s) sélectionné(s) ?`)) {
            event.preventDefault();
        }
    });
    updateSelection();
});

const brandingForm = document.querySelector('[data-branding-form]');
if (brandingForm) {
    const logoInput = brandingForm.querySelector('[data-brand-logo-input]');
    const logoPreview = brandingForm.querySelector('[data-brand-logo-preview]');
    const primaryColorInput = brandingForm.querySelector('[data-brand-primary]');
    const accentColorInput = brandingForm.querySelector('[data-brand-accent]');
    const paletteStatus = brandingForm.querySelector('[data-brand-palette-status]');
    let previewUrl = null;

    const applyBrandColors = () => {
        document.documentElement.style.setProperty('--brand-primary', primaryColorInput.value);
        document.documentElement.style.setProperty('--brand-accent', accentColorInput.value);
    };

    const colorHex = ({ red, green, blue, count }) => `#${[red, green, blue]
        .map((total) => Math.round(total / count).toString(16).padStart(2, '0'))
        .join('')}`;
    const colorDistance = (first, second) => {
        const channels = (hex) => hex.match(/[\da-f]{2}/gi).map((channel) => Number.parseInt(channel, 16));
        const firstChannels = channels(first);
        const secondChannels = channels(second);
        return Math.hypot(...firstChannels.map((channel, index) => channel - secondChannels[index]));
    };

    logoInput.addEventListener('change', () => {
        const file = logoInput.files?.[0];
        if (!file) return;

        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);
        logoPreview.src = previewUrl;
        logoPreview.hidden = false;

        const image = new Image();
        image.onerror = () => {
            paletteStatus.textContent = 'Impossible de lire cette image. Choisissez un PNG, JPG ou WebP valide.';
        };
        image.onload = () => {
            const scale = Math.min(1, 64 / Math.max(image.naturalWidth, image.naturalHeight));
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
            canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
            const context = canvas.getContext('2d', { willReadFrequently: true });
            context.drawImage(image, 0, 0, canvas.width, canvas.height);

            const pixels = context.getImageData(0, 0, canvas.width, canvas.height).data;
            const colorfulBuckets = new Map();
            const allBuckets = new Map();
            for (let index = 0; index < pixels.length; index += 4) {
                const [red, green, blue, alpha] = pixels.slice(index, index + 4);
                if (alpha < 150) continue;

                const maximum = Math.max(red, green, blue);
                const minimum = Math.min(red, green, blue);
                if (maximum > 244 || maximum < 28) continue;

                const key = [red >> 4, green >> 4, blue >> 4].join('-');
                for (const buckets of [allBuckets, ...(maximum - minimum > 28 ? [colorfulBuckets] : [])]) {
                    const bucket = buckets.get(key) || { red: 0, green: 0, blue: 0, count: 0 };
                    bucket.red += red;
                    bucket.green += green;
                    bucket.blue += blue;
                    bucket.count += 1;
                    buckets.set(key, bucket);
                }
            }

            const rankBuckets = (buckets) => [...buckets.values()]
                .sort((first, second) => second.count - first.count)
                .map(colorHex);
            const palette = rankBuckets(colorfulBuckets).length ? rankBuckets(colorfulBuckets) : rankBuckets(allBuckets);
            if (!palette.length) {
                paletteStatus.textContent = 'Aucune couleur dominante détectée. Choisissez les couleurs manuellement.';
                return;
            }

            primaryColorInput.value = palette[0];
            const accentColor = palette.find((color) => colorDistance(color, palette[0]) > 80) || palette[1];
            if (accentColor) accentColorInput.value = accentColor;
            applyBrandColors();
            paletteStatus.textContent = 'Couleurs détectées. Vous pouvez les ajuster avant d’enregistrer.';
        };
        image.src = previewUrl;
    });

    primaryColorInput.addEventListener('input', applyBrandColors);
    accentColorInput.addEventListener('input', applyBrandColors);
}

document.addEventListener('change', (event) => {
    const templateSelect = event.target.closest('[data-resume-template-photo-select]');
    if (templateSelect) {
        const photoToggle = templateSelect.form?.querySelector('[name="customization[include_photo]"]');
        if (photoToggle) {
            const photoEnabled = templateSelect.selectedOptions[0]?.dataset.photoEnabled === '1';
            photoToggle.disabled = !photoEnabled;
            photoToggle.checked = photoEnabled;
        }
        return;
    }

    const select = event.target.closest('[data-resume-photo-select]');
    if (!select) return;

    const control = select.closest('[data-resume-photo-control]');
    const preview = control?.querySelector('[data-resume-photo-preview]');
    if (!preview) return;

    const selectedOption = select.selectedOptions[0];
    preview.src = selectedOption?.dataset.photoUrl || select.dataset.defaultPhotoUrl || '';
    preview.classList.toggle('d-none', !preview.src);

    if (select.value) {
        const photoToggle = select.form?.querySelector('[name="customization[include_photo]"]');
        if (photoToggle) photoToggle.checked = true;
    }
});

