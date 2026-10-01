(function () {
    if (!window.IS_LOGGED_IN) return;

    const OPTIONS = window.MII_OPTIONS;
    const CATEGORY_KEYS = Object.keys(OPTIONS.categories);

    let state = Object.assign({}, OPTIONS.defaults);
    let activeCategory = CATEGORY_KEYS[0];

    const canvas = document.getElementById('mii-canvas');
    const optionsPanel = document.getElementById('mii-options');
    const colorsPanel = document.getElementById('mii-colors');
    const statusEl = document.getElementById('mii-status');
    const confirmBtn = document.getElementById('confirm');
    function mirror(markup) {
        return markup + `<g transform="translate(300,0) scale(-1,1)">${markup}</g>`;
    }

    function el(tag, attrs, html) {
        const node = document.createElement(tag);
        Object.entries(attrs || {}).forEach(([k, v]) => node.setAttribute(k, v));
        if (html !== undefined) node.innerHTML = html;
        return node;
    }

    function renderFace(idx, skin) {
        const shapes = [
            `<ellipse cx="150" cy="145" rx="64" ry="78" fill="${skin}"/>`,
            `<ellipse cx="150" cy="145" rx="72" ry="70" fill="${skin}"/>`,
            `<rect x="84" y="72" width="132" height="146" rx="24" fill="${skin}"/>`,
            `<path d="M86,110 C86,58 214,58 214,110 C214,150 196,165 150,215 C104,165 86,150 86,110 Z" fill="${skin}"/>`,
            `<ellipse cx="150" cy="150" rx="56" ry="92" fill="${skin}"/>`,
        ];
        return shapes[idx] || shapes[0];
    }

    function renderEars(skin) {
        return mirror(`<ellipse cx="78" cy="145" rx="12" ry="20" fill="${skin}"/>`);
    }

    function renderHair(idx, color) {
        const styles = [
            ``, // 0 bald
            `<path d="M78,118 C78,46 222,46 222,118 L222,86 C222,60 78,60 78,86 Z" fill="${color}"/>`,
            `<path d="M78,110 L90,55 L105,95 L120,40 L135,90 L150,35 L165,90 L180,40 L195,95 L210,55 L222,110 L222,80 Q150,55 78,80 Z" fill="${color}"/>`,
            `<path d="M78,120 C78,48 222,48 222,120 L222,88 C222,62 78,62 78,88 Z" fill="${color}"/>
             <path d="M80,110 C70,160 68,220 78,260 L96,260 C90,210 92,150 100,112 Z" fill="${color}"/>
             <path d="M220,110 C230,160 232,220 222,260 L204,260 C210,210 208,150 200,112 Z" fill="${color}"/>`,
            `<path d="M78,118 C78,46 222,46 222,118 L222,86 C222,60 78,60 78,86 Z" fill="${color}"/>
             <circle cx="150" cy="40" r="16" fill="${color}"/>`,
            `<circle cx="150" cy="82" r="58" fill="${color}"/>`,
            `<path d="M138,110 C130,70 132,30 150,20 C168,30 170,70 162,110 Z" fill="${color}"/>`,
            `<path d="M78,115 C78,50 222,50 222,115 L222,85 C210,55 90,55 78,90 Z" fill="${color}"/>
             <path d="M90,90 C110,70 140,65 170,85 C150,95 110,100 90,90 Z" fill="${color}"/>`,
        ];
        return styles[idx] || styles[0];
    }

    function renderEyebrows(idx, color) {
        const styles = [
            `<rect x="100" y="100" width="34" height="4" rx="2" fill="${color}"/>`,
            `<rect x="98" y="98" width="36" height="7" rx="3" fill="${color}"/>`,
            `<path d="M98,104 Q116,90 134,100" stroke="${color}" stroke-width="5" fill="none" stroke-linecap="round"/>`,
            `<path d="M98,96 L134,108" stroke="${color}" stroke-width="6" stroke-linecap="round"/>`,
            `<path d="M96,100 Q116,78 136,98" stroke="${color}" stroke-width="5" fill="none" stroke-linecap="round"/>`,
            `<path d="M96,102 Q116,88 136,100" stroke="${color}" stroke-width="9" fill="none" stroke-linecap="round"/>`,
        ];
        return mirror(styles[idx] || styles[0]);
    }

    function renderEyes(idx, eyeColor, skin) {
        const styles = [
            `<circle cx="116" cy="128" r="14" fill="#fff" stroke="#2b2b2b" stroke-width="2"/><circle cx="116" cy="128" r="7" fill="${eyeColor}"/><circle cx="116" cy="128" r="3" fill="#111"/>`,
            `<ellipse cx="116" cy="128" rx="16" ry="9" fill="#fff" stroke="#2b2b2b" stroke-width="2"/><circle cx="116" cy="128" r="6" fill="${eyeColor}"/><circle cx="116" cy="128" r="2.5" fill="#111"/>`,
            `<ellipse cx="116" cy="128" rx="16" ry="9" fill="#fff" stroke="#2b2b2b" stroke-width="2"/><circle cx="116" cy="130" r="6" fill="${eyeColor}"/><path d="M100,122 Q116,116 132,122 L132,125 Q116,120 100,125 Z" fill="${skin}"/>`,
            `<circle cx="116" cy="128" r="18" fill="#fff" stroke="#2b2b2b" stroke-width="2"/><circle cx="116" cy="128" r="8" fill="${eyeColor}"/><circle cx="116" cy="128" r="3.5" fill="#111"/>`,
            `<path d="M102,130 Q116,114 130,130" stroke="#2b2b2b" stroke-width="4" fill="none" stroke-linecap="round"/>`,
            `<g transform="rotate(-12 116 128)"><ellipse cx="116" cy="128" rx="17" ry="8" fill="#fff" stroke="#2b2b2b" stroke-width="2"/><circle cx="119" cy="128" r="6" fill="${eyeColor}"/><circle cx="119" cy="128" r="2.5" fill="#111"/></g>`,
            `<rect x="102" y="118" width="28" height="20" rx="4" fill="#fff" stroke="#2b2b2b" stroke-width="2"/><circle cx="116" cy="128" r="6" fill="${eyeColor}"/><circle cx="116" cy="128" r="2.5" fill="#111"/>`,
            `<ellipse cx="116" cy="128" rx="16" ry="10" fill="#fff" stroke="#2b2b2b" stroke-width="2"/><circle cx="116" cy="129" r="6" fill="${eyeColor}"/><circle cx="116" cy="129" r="2.5" fill="#111"/><path d="M101,133 Q116,140 131,133 Z" fill="${skin}"/>`,
        ];
        return mirror(styles[idx] || styles[0]);
    }

    function renderNose(idx) {
        const styles = [
            `<circle cx="150" cy="146" r="3" fill="rgba(0,0,0,0.35)"/>`,
            `<ellipse cx="150" cy="146" rx="6" ry="5" fill="rgba(0,0,0,0.12)" stroke="rgba(0,0,0,0.25)" stroke-width="1"/>`,
            `<line x1="150" y1="128" x2="150" y2="150" stroke="rgba(0,0,0,0.3)" stroke-width="3" stroke-linecap="round"/>`,
            `<path d="M146,126 Q138,145 148,152" stroke="rgba(0,0,0,0.3)" stroke-width="3" fill="none" stroke-linecap="round"/>`,
            `<path d="M142,150 L158,150 L150,128 Z" fill="none" stroke="rgba(0,0,0,0.25)" stroke-width="2"/>`,
            `<path d="M146,130 Q140,148 152,150 Q158,150 156,144" stroke="rgba(0,0,0,0.3)" stroke-width="3" fill="none" stroke-linecap="round"/>`,
        ];
        return styles[idx] || styles[0];
    }

    function renderMouth(idx) {
        const styles = [
            `<path d="M128,182 Q150,200 172,182" stroke="#8a3b3b" stroke-width="4" fill="none" stroke-linecap="round"/>`,
            `<line x1="130" y1="186" x2="170" y2="186" stroke="#8a3b3b" stroke-width="4" stroke-linecap="round"/>`,
            `<path d="M126,180 Q150,206 174,180 Q150,196 126,180 Z" fill="#5e2323"/><path d="M132,183 Q150,190 168,183 L166,187 Q150,194 134,187 Z" fill="#fff"/>`,
            `<path d="M128,186 Q150,196 174,178" stroke="#8a3b3b" stroke-width="4" fill="none" stroke-linecap="round"/>`,
            `<ellipse cx="150" cy="188" rx="10" ry="13" fill="#5e2323"/>`,
            `<path d="M128,196 Q150,180 172,196" stroke="#8a3b3b" stroke-width="4" fill="none" stroke-linecap="round"/>`,
            `<ellipse cx="150" cy="188" rx="8" ry="5" fill="#b5585f"/>`,
            `<path d="M120,180 Q150,212 180,180 Q150,202 120,180 Z" fill="#5e2323"/><rect x="128" y="183" width="44" height="7" rx="3" fill="#fff"/>`,
        ];
        return styles[idx] || styles[0];
    }

    function renderFacialHair(idx, color) {
        if (idx === null || idx === undefined) return '';
        const styles = [
            `<path d="M122,172 Q136,164 150,172 Q164,164 178,172 Q164,178 150,174 Q136,178 122,172 Z" fill="${color}"/>`,
            `<path d="M90,150 C86,190 108,220 150,224 C192,220 214,190 210,150 C210,185 190,205 150,208 C110,205 90,185 90,150 Z" fill="${color}" opacity="0.95"/>`,
            `<path d="M122,172 Q150,164 178,172 Q164,178 150,174 Q136,178 122,172 Z" fill="${color}"/><path d="M136,196 Q150,222 164,196 Q150,208 136,196 Z" fill="${color}"/>`,
            `<g fill="${color}" opacity="0.5">
                <circle cx="108" cy="178" r="1.6"/><circle cx="118" cy="188" r="1.6"/><circle cx="130" cy="196" r="1.6"/>
                <circle cx="145" cy="200" r="1.6"/><circle cx="160" cy="200" r="1.6"/><circle cx="175" cy="196" r="1.6"/>
                <circle cx="188" cy="188" r="1.6"/><circle cx="198" cy="178" r="1.6"/><circle cx="128" cy="178" r="1.6"/><circle cx="172" cy="178" r="1.6"/>
             </g>`,
            `<rect x="146" y="196" width="8" height="10" rx="3" fill="${color}"/>`,
        ];
        return styles[idx] || '';
    }

    function renderGlasses(idx) {
        if (idx === null || idx === undefined) return '';
        const styles = [
            `<g fill="none" stroke="#2b2b2b" stroke-width="3"><circle cx="116" cy="128" r="22"/><circle cx="184" cy="128" r="22"/><line x1="138" y1="128" x2="162" y2="128"/></g>`,
            `<g fill="none" stroke="#2b2b2b" stroke-width="3"><rect x="94" y="110" width="44" height="34" rx="6"/><rect x="162" y="110" width="44" height="34" rx="6"/><line x1="138" y1="126" x2="162" y2="126"/></g>`,
            `<g><rect x="94" y="112" width="44" height="30" rx="8" fill="#222" opacity="0.85"/><rect x="162" y="112" width="44" height="30" rx="8" fill="#222" opacity="0.85"/><line x1="138" y1="124" x2="162" y2="124" stroke="#222" stroke-width="3"/></g>`,
            `<g fill="none" stroke="#2b2b2b" stroke-width="3"><path d="M94,132 Q94,108 120,110 Q140,112 138,132 Q116,124 94,132 Z"/><path d="M206,132 Q206,108 180,110 Q160,112 162,132 Q184,124 206,132 Z"/><line x1="138" y1="122" x2="162" y2="122"/></g>`,
            `<g fill="none" stroke="#2b2b2b" stroke-width="2"><rect x="96" y="116" width="42" height="24" rx="4"/><rect x="162" y="116" width="42" height="24" rx="4"/><line x1="138" y1="126" x2="162" y2="126"/></g>`,
        ];
        return styles[idx] || '';
    }

    function renderShirt(idx, sc, skin) {
        const base = `<path d="M70,380 L70,290 Q70,248 112,233 L150,224 L188,233 Q230,248 230,290 L230,380 Z" fill="${sc}"/>`;
        const styles = [
            base + `<path d="M126,228 Q150,244 174,228 L174,236 Q150,250 126,236 Z" fill="${sc}" opacity="0.6"/>`,
            base + `<path d="M130,230 L150,270 L170,230 L162,228 L150,254 L138,228 Z" fill="${skin}"/>`,
            `<path d="M60,300 Q60,250 110,232 L120,260 Q80,275 70,310 Z" fill="${sc}" opacity="0.85"/>
             <path d="M240,300 Q240,250 190,232 L180,260 Q220,275 230,310 Z" fill="${sc}" opacity="0.85"/>
             ${base}
             <circle cx="140" cy="245" r="2.5" fill="#fff"/><circle cx="160" cy="245" r="2.5" fill="#fff"/>
             <line x1="140" y1="245" x2="138" y2="265" stroke="#fff" stroke-width="2"/>
             <line x1="160" y1="245" x2="162" y2="265" stroke="#fff" stroke-width="2"/>`,
            base + `<path d="M138,228 L150,224 L150,250 Z" fill="#fff"/><path d="M162,228 L150,224 L150,250 Z" fill="#fff"/>
             <line x1="150" y1="250" x2="150" y2="340" stroke="rgba(0,0,0,0.15)" stroke-width="2"/>
             <circle cx="150" cy="265" r="2" fill="rgba(0,0,0,0.3)"/><circle cx="150" cy="290" r="2" fill="rgba(0,0,0,0.3)"/><circle cx="150" cy="315" r="2" fill="rgba(0,0,0,0.3)"/>`,
            base + `<g stroke="#fff" stroke-width="8" opacity="0.85"><line x1="72" y1="300" x2="228" y2="300"/><line x1="70" y1="330" x2="230" y2="330"/><line x1="70" y1="360" x2="230" y2="360"/></g>`,
            base + `<path d="M136,230 L150,226 L150,244 Z" fill="${sc}" opacity="0.5" stroke="#fff" stroke-width="1"/>
             <path d="M164,230 L150,226 L150,244 Z" fill="${sc}" opacity="0.5" stroke="#fff" stroke-width="1"/>
             <line x1="150" y1="244" x2="150" y2="270" stroke="rgba(0,0,0,0.2)" stroke-width="2"/>
             <circle cx="150" cy="252" r="1.8" fill="rgba(0,0,0,0.3)"/><circle cx="150" cy="264" r="1.8" fill="rgba(0,0,0,0.3)"/>`,
            `<path d="M90,380 L90,300 Q90,270 115,255 L130,230 L170,230 L185,255 Q210,270 210,300 L210,380 Z" fill="${sc}"/>
             <rect x="128" y="222" width="10" height="20" rx="4" fill="${sc}"/><rect x="162" y="222" width="10" height="20" rx="4" fill="${sc}"/>`,
            base + `<rect x="130" y="196" width="40" height="42" rx="18" fill="${sc}"/>`,
        ];
        return styles[idx] || styles[0];
    }

    function fullMiiSvg(s) {
        let svg = '';
        svg += renderShirt(s.shirt, s.shirt_color, s.skin_color);
        svg += `<rect x="135" y="196" width="30" height="40" fill="${s.skin_color}"/>`;
        svg += renderEars(s.skin_color);
        svg += renderFace(s.face_shape, s.skin_color);
        svg += renderHair(s.hair, s.hair_color);
        svg += renderEyebrows(s.eyebrows, s.hair_color);
        svg += renderEyes(s.eyes, s.eye_color, s.skin_color);
        svg += renderNose(s.nose);
        svg += renderMouth(s.mouth);
        svg += renderFacialHair(s.facial_hair, s.hair_color);
        svg += renderGlasses(s.glasses);
        return svg;
    }

    function renderCanvas() {
        canvas.innerHTML = fullMiiSvg(state);
    }

    const THUMB_CROP = {
        face_shape: '70 50 160 190',
        hair: '40 10 220 300',
        eyebrows: '85 85 130 35',
        eyes: '85 105 130 50',
        nose: '125 115 50 50',
        mouth: '110 170 80 50',
        facial_hair: '75 150 150 85',
        glasses: '80 95 140 60',
        shirt: '50 215 200 170',
    };

    function thumbMarkup(category, idx) {
        switch (category) {
            case 'face_shape': return renderFace(idx, state.skin_color);
            case 'hair': return renderHair(idx, state.hair_color);
            case 'eyebrows': return renderEyebrows(idx, state.hair_color);
            case 'eyes': return renderEyes(idx, state.eye_color, state.skin_color);
            case 'nose': return renderNose(idx);
            case 'mouth': return renderMouth(idx);
            case 'facial_hair': return idx === null ? '' : renderFacialHair(idx, state.hair_color);
            case 'glasses': return idx === null ? '' : renderGlasses(idx);
            case 'shirt': return renderShirt(idx, state.shirt_color, state.skin_color);
            default: return '';
        }
    }
    function buildOptionsPanel() {
        const meta = OPTIONS.categories[activeCategory];
        optionsPanel.innerHTML = '';

        const indices = [];
        if (meta.nullable) indices.push(null);
        for (let i = 0; i < meta.count; i++) indices.push(i);

        indices.forEach((idx) => {
            const isActive = state[activeCategory] === idx;
            const btn = el('button', {
                type: 'button',
                class: 'mii-thumb' + (isActive ? ' active' : ''),
                title: idx === null ? 'None' : `${meta.label} ${idx + 1}`,
            });

            if (idx === null) {
                btn.classList.add('mii-thumb-none');
                btn.textContent = 'None';
            } else {
                const svg = el('svg', { viewBox: THUMB_CROP[activeCategory] }, thumbMarkup(activeCategory, idx));
                btn.appendChild(svg);
            }

            btn.addEventListener('click', () => {
                state[activeCategory] = idx;
                renderCanvas();
                buildOptionsPanel();
            });

            optionsPanel.appendChild(btn);
        });

        buildColorSwatches();
    }

    function buildColorSwatches() {
        const meta = OPTIONS.categories[activeCategory];
        if (!meta.colorField) {
            colorsPanel.hidden = true;
            colorsPanel.innerHTML = '';
            return;
        }

        colorsPanel.hidden = false;
        colorsPanel.innerHTML = '';
        const field = meta.colorField;
        const palette = OPTIONS.palettes[field] || [];

        palette.forEach((hex) => {
            const isActive = (state[field] || '').toLowerCase() === hex.toLowerCase();
            const swatch = el('button', {
                type: 'button',
                class: 'mii-swatch' + (isActive ? ' active' : ''),
                style: `background:${hex}`,
                title: hex,
            });
            swatch.addEventListener('click', () => {
                state[field] = hex;
                renderCanvas();
                buildOptionsPanel();
            });
            colorsPanel.appendChild(swatch);
        });

        const customWrap = el('label', { class: 'mii-swatch mii-swatch-custom', title: 'Custom color' });
        const customInput = el('input', { type: 'color', value: state[field] || '#000000' });
        customInput.addEventListener('input', (e) => {
            state[field] = e.target.value;
            renderCanvas();
            buildOptionsPanel();
        });
        customWrap.appendChild(customInput);
        colorsPanel.appendChild(customWrap);
    }

    function setActiveCategory(key) {
        activeCategory = key;
        document.querySelectorAll('.selectbox').forEach((box) => {
            box.classList.toggle('selected', box.dataset.category === key);
        });
        buildOptionsPanel();
    }

    document.querySelectorAll('.selectbox').forEach((box) => {
        box.addEventListener('click', () => setActiveCategory(box.dataset.category));
    });

    function showStatus(target, message, isError) {
        target.textContent = message;
        target.className = 'form-message' + (isError ? ' is-error' : ' is-success');
        if (!isError) {
            setTimeout(() => {
                if (target.textContent === message) {
                    target.textContent = '';
                    target.className = 'form-message';
                }
            }, 3000);
        }
    }


    async function loadMii() {
        try {
            const res = await fetch('mii_api.php?action=load');
            const data = await res.json();
            if (data.success) {
                state = Object.assign({}, OPTIONS.defaults, data.mii);
            }
        } catch (e) {
            showStatus(statusEl, 'Could not load your Mii.', true);
        }
        renderCanvas();
        buildOptionsPanel();
    }

    async function saveMii() {
        confirmBtn.disabled = true;
        try {
            const res = await fetch('mii_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(Object.assign({ action: 'save' }, state)),
            });
            const data = await res.json();
            showStatus(statusEl, data.success ? data.message : data.error, !data.success);
        } catch (e) {
            showStatus(statusEl, 'Could not save your Mii. Please try again.', true);
        } finally {
            confirmBtn.disabled = false;
        }
    }

    confirmBtn.addEventListener('click', saveMii);

    const profileForm = document.getElementById('profile-form');
    const profileStatus = document.getElementById('profile-status');

    profileForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(profileForm);
        const newPassword = fd.get('new_password') || '';
        const confirmPassword = fd.get('confirm_password') || '';
        const currentPassword = fd.get('current_password') || '';

        const payload = {
            action: 'save_profile',
            name: fd.get('name') || '',
            email: fd.get('email') || '',
            gender: fd.get('gender') || '',
            birthday: fd.get('birthday') || '',
            favorite_color: fd.get('favorite_color') || '',
            is_single: fd.get('is_single') === 'on',
        };

        try {
            const res = await fetch('mii_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!data.success) {
                showStatus(profileStatus, data.error, true);
                return;
            }

            if (newPassword || confirmPassword || currentPassword) {
                const pwRes = await fetch('mii_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'change_password',
                        current_password: currentPassword,
                        new_password: newPassword,
                        confirm_password: confirmPassword,
                    }),
                });
                const pwData = await pwRes.json();
                if (!pwData.success) {
                    showStatus(profileStatus, pwData.error, true);
                    return;
                }
                profileForm.new_password.value = '';
                profileForm.confirm_password.value = '';
                profileForm.current_password.value = '';
            }

            showStatus(profileStatus, 'Profile saved!', false);
        } catch (err) {
            showStatus(profileStatus, 'Could not save your profile. Please try again.', true);
        }
    });

    loadMii();
})();
