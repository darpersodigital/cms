// ===== AI SEO Checker (ES Module, Laravel API version) =====

// Get SEO input field dynamically
export function getSeoInput(field, language) {
    return document.querySelector(`input[name*="${language}[${field}]"]`);
}

// Check whether a SEO field is required for this post type (set via the field builder's
// "nullable" config and rendered as data-required on the input by the Blade field templates)
export function isSeoFieldRequired(field, language) {
    const input = getSeoInput(field, language);
    return input?.dataset.required === '1';
}

// Check image validity
export function checkImage(language) {
    const imageInput = getSeoInput('seo_image', language);
    const imageContainer = imageInput ? imageInput.closest(".input-wrapper")?.querySelector('.img-container') : null;
    const imageElement = imageContainer ? imageContainer.querySelector('img') : null;

    if (imageElement && imageElement.src && imageElement.src !== "") {
        return "Image tag is set and valid!";
    } else if (imageInput && imageInput.files && imageInput.files.length > 0) {
        return "Image file is selected and valid!";
    } else if (isSeoFieldRequired('seo_image', language)) {
        return "Missing SEO Image!";
    } else {
        return null;
    }
}

// Run the image check and render its result under the input (shared by the batch
// AI/fallback pass and the live, no-network validation below)
export function checkAndRenderImage(language) {
    const message = checkImage(language);
    const score = message === null
        ? null
        : (message === "Image tag is set and valid!" || message === "Image file is selected and valid!") ? 10 : 0;
    appendSeoResults('seo_image', language, message, score);
    return { message, score };
}

// Append SEO results under input
export function appendSeoResults(field, language, message, score) {
    const fieldInput = getSeoInput(field, language);
    if (!fieldInput) return;

    const existingDiv = fieldInput.parentNode.parentNode.parentNode.querySelector('.seo-message-container');
    if (existingDiv) existingDiv.remove();

    // score === null means the field is optional and empty - nothing to report
    if (score === null || score === undefined) return;

    const resultDiv = document.createElement('div');
    resultDiv.className = 'seo-message-container';
    resultDiv.innerHTML = score <= 10
        ? `<div class="${score < 6 ? 'text-error' : 'error-text'}" style="color:${getTextColor(score)};">${message}</div>${renderProgressBar(score)}`
        : `${renderProgressBar(score)}`;
    fieldInput.parentNode.parentNode.parentNode.appendChild(resultDiv);
}

// Progress bar helpers
export function renderProgressBar(score) {
    if (score > 0) {
        const percentage = score * 10;
        return `<div class="progress-bar-container">
                <div class="progress-bar" style="width: ${percentage}%; background-color: ${getProgressBarColor(percentage)};">
                    <span class="progress-score">${score}</span>
                </div>
            </div>`;
    } else return ""

}

export function getProgressBarColor(percentage) {
    if (percentage >= 80) return '#2faa7e';
    if (percentage >= 60) return '#fbc02d';
    return '#dc3545';
}

export function getTextColor(score) {
    if (score >= 6) return 'black';
    return '#dc3545';
}
function isGibberish(text) {
    if (!text) return true;

    const ignoredWords = ['if', 'you', 'are', 'the', 'this', 'because', 'and', 'to', 'in', 'for', 'on', 'at', 'with', 'it', 'that', 'by', 'from', 'of'];
    const lowerText = text.toLowerCase();
    if (ignoredWords.some(word => lowerText.includes(word))) return false;

    // Detect repeated letters (aaa, bbb) or symbols
    const repeatedLetterPattern = /([a-zA-Z])\1{2,}/;
    const symbolPattern = /[^\w\s]/;

    // Detect numeric-only sequences
    const numericPattern = /^[0-9]+$/; // only digits
    const repeatedDigitsPattern = /(\d)\1{2,}/; // repeated digits like 111, 2222

    // Check for gibberish if any pattern matches
    if (repeatedLetterPattern.test(text)) return true;
    if (symbolPattern.test(text) && !/\s/.test(text)) return true; // symbols without spaces
    if (numericPattern.test(text)) return true;
    if (repeatedDigitsPattern.test(text)) return true;

    // Optionally, very short nonsense: less than 3 characters overall
    if (text.trim().length < 3) return true;

    // Otherwise, it's probably valid
    return false;
}
// Fallback checker with gibberish detection
export function fallbackToCustomQualityChecker(content, field, required = true) {
    let score = 10, message = "Content is well-sized.";
    const length = content?.trim().length || 0;

    // Empty and not required for this post type - nothing to flag
    if (length === 0 && !required) {
        return { score: null, message: null };
    }

    if (length > 0 && isGibberish(content)) {
        return { score: 0, message: "Content appears to be gibberish. Please rewrite it." };
    }

    if (field === 'seo_title') {
        if (length === 0) {
            score = 0;
            message = "Missing SEO Title!";
        }
        else if (length < 40) {
            score = 3;
            message = "Title is too short. Aim for 50-60 characters.";
        }
        else if (length >= 40 && length < 50) {
            score = 7;
            message = "Title is acceptable, but 50-60 characters is ideal.";
        }
        else if (length >= 50 && length <= 60) {
            score = 10;
            message = "SEO Title length is ideal.";
        }
        else if (length > 60 && length <= 70) {
            score = 8;
            message = "Title is slightly long, but still acceptable.";
        }
        else {
            score = 5;
            message = "Title is too long. Try to keep it under 60 characters if possible.";
        }

    } else if (field === 'seo_page_title') {
        if (length === 0) {
            score = 0;
            message = "Missing SEO Page Title!";
        }
        else if (length < 40) {
            score = 3;
            message = "Page Title is too short. Aim for 50-60 characters.";
        }
        else if (length >= 40 && length < 50) {
            score = 7;
            message = "Page Title is acceptable, but 50-60 characters is ideal.";
        }
        else if (length >= 50 && length <= 60) {
            score = 10;
            message = "SEO Page Title length is ideal.";
        }
        else if (length > 60 && length <= 70) {
            score = 8;
            message = "Page Title is slightly long, but still acceptable.";
        }
        else {
            score = 5;
            message = "Page Title is too long. Try to keep it under 60 characters if possible.";
        }

    } else if (field === 'seo_description') {
        if (length === 0) {
            score = 0;
            message = "Missing SEO Description!";
        }
        else if (length < 130) {
            score = 3;
            message = "Description is too short. Aim for 150-160 characters.";
        }
        else if (length >= 130 && length < 150) {
            score = 7;
            message = "Description is acceptable, but 150-160 characters is ideal.";
        }
        else if (length >= 150 && length <= 160) {
            score = 10;
            message = "SEO Description length is ideal.";
        }
        else if (length > 160 && length <= 180) {
            score = 8;
            message = "Description is slightly long, but still acceptable.";
        }
        else {
            score = 5;
            message = "Description is too long and may be truncated in search results.";
        }
    } else if (field === 'seo_keywords') {
        const keywords = content
            ?.split(',')
            .map(keyword => keyword.trim())
            .filter(Boolean) || [];

        const count = keywords.length;

        if (count === 0) {
            score = 0;
            message = "Missing SEO Keywords!";
        }
        else if (count < 3) {
            score = 4;
            message = "Too few keywords. Aim for 3-8 focused keywords.";
        }
        else if (count >= 3 && count <= 8) {
            score = 10;
            message = "SEO Keywords count is ideal.";
        }
        else if (count > 8 && count <= 10) {
            score = 8;
            message = "Keyword count is acceptable, but 3-8 focused keywords is ideal.";
        }
        else {
            score = 4;
            message = "Too many keywords. Keep it under 10 and avoid keyword stuffing.";
        }
    } else if (field === 'seo_author' && content) {

        if (length === 0) {
            score = 0;
            message = "Missing SEO Author!";
        }
        else if (length < 3) {
            score = 3;
            message = "Author name is too short.";
        }
        else if (length >= 3 && length <= 50) {
            score = 10;
            message = "SEO Author length is valid.";
        }
        else if (length > 50 && length <= 70) {
            score = 7;
            message = "Author name is slightly long, but still acceptable.";
        }
        else {
            score = 4;
            message = "Author name is too long. Try to keep it under 50 characters.";
        }

    } else {
        if (content?.length == 0) { score = 0; message = "Missing !" }
        else if (content.length < 50) { score = 3; message = "Content is too short. Consider at least 50 characters."; }
        else if (content.length > 100) { score = 6; message = "Content is too long. Consider shortening."; }
        else { score = 10; }
    }

    return { score, message };
}

// Collect all fields
export function collectFields(language) {
    return {
        seo_title: getSeoInput('seo_title', language)?.value || "",
        seo_page_title: getSeoInput('seo_page_title', language)?.value || "",
        seo_description: getSeoInput('seo_description', language)?.value || "",
        seo_keywords: getSeoInput('seo_keywords', language)?.value || "",
        seo_author: getSeoInput('seo_author', language)?.value || "",
        seo_robots: getSeoInput('seo_robots', language)?.value || "",
        seo_image: getSeoInput('seo_image', language)?.value || ""
    };
}

export function getSeoLanguages() {
    const seoTitleInputs = document.querySelectorAll('[name$="[seo_title]"]');
    const languages = new Set();

    for (const input of seoTitleInputs) {
        const matches = input.name.match(/([^[\]]+)\[seo_title\]$/);
        if (matches?.[1]) {
            languages.add(matches[1]);
        }
    }

    return Array.from(languages);
}

// New implementation: call Laravel endpoint instead of direct OpenAI



// Apply results to UI
export function applySeoResults(language, aiResults) {
    for (const field in aiResults) {
        appendSeoResults(field, language, aiResults[field].message, aiResults[field].score);
    }
}
export async function callSeoAI(allFields) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    try {
        const response = await fetch('/admin/analyze-seo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ fields: allFields })
        });
        const data = await response.json();

        if (data.success && data.results) return data.results;

        console.warn("AI analysis failed: invalid response");
        return null;
    } catch (err) {
        console.error("AI Analysis error:", err);
        return null;
    }
}
// --- Main SEO checker with full fallback ---
export async function checkSEOHealthWithAI(token, withLoader = true) {
    const languages = getSeoLanguages();

    console.log("languages ", languages)
    if (languages.length === 0) return {};

    const seoHealth = {};

    // Collect all fields for all languages
    const allFields = {};
    for (const lang of languages) {
        allFields[lang] = collectFields(lang);
    }

    // Call API once for all languages
    let aiResultsAllLanguages
    if (token?.length > 0) {
        aiResultsAllLanguages = await callSeoAI(allFields);
    }

    // If API fails, use fallback for all languages
    if (!aiResultsAllLanguages && (!token || token?.length > 0)) {
        aiResultsAllLanguages = {};
        for (const lang of languages) {
            aiResultsAllLanguages[lang] = {};
            const fields = allFields[lang];
            for (const field in fields) {
                if (!["seo_image", "seo_robots"].includes(field)) {
                    const required = isSeoFieldRequired(field, lang);
                    aiResultsAllLanguages[lang][field] = fallbackToCustomQualityChecker(fields[field], field, required);
                }
            }
        }
    }

    // Apply results and custom checks per language
    for (const lang of languages) {
        const fields = allFields[lang];
        const aiResults = aiResultsAllLanguages[lang] || {};

        // Apply AI / fallback results
        applySeoResults(lang, aiResults);

        // Custom checks for seo_image
        const { message: imageMessage, score: imageScore } = checkAndRenderImage(lang);

        // Custom checks for seo_robots
        const robotsInput = getSeoInput('seo_robots', lang);
        if (robotsInput) {
            const robotsValue = robotsInput.value;
            const robotsRequired = isSeoFieldRequired('seo_robots', lang);
            const robotsMessage = robotsValue ? "Robots field is set" : (robotsRequired ? "Robots field is empty" : null);
            const robotsScore = robotsValue ? 10 : (robotsRequired ? 0 : null);
            appendSeoResults('seo_robots', lang, robotsMessage, robotsScore);

            seoHealth[lang] = {
                ...aiResults,
                seo_image: { message: imageMessage, score: imageScore },
                seo_robots: { message: robotsMessage, score: robotsScore }
            };
        } else {
            seoHealth[lang] = {
                ...aiResults,
                seo_image: { message: imageMessage, score: imageScore }
            };
        }
    }

    if (withLoader) setTimeout(() => $('.admin-loader-wrapper').addClass('loaded'), 750);

    console.log("SEO Health Results:", seoHealth);
    return seoHealth;
}

// --- Live, no-network validation (runs on input/blur, independent of the AI call) ---

function debounce(fn, delay = 300) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

// Re-runs the local fallback checker (no network) for a single field and renders it,
// so a required field left empty (or a quality issue) surfaces immediately instead of
// waiting for the next full AI pass on page load / "Check SEO" click / submit.
export function validateSeoFieldLive(field, language) {
    const input = getSeoInput(field, language);
    if (!input) return;

    const required = isSeoFieldRequired(field, language);
    const { score, message } = fallbackToCustomQualityChecker(input.value, field, required);
    appendSeoResults(field, language, message, score);
}

// Wires live validation listeners to every SEO field for every language present on the form.
// Live checks only start once a field has held a value - an untouched empty field (e.g. one
// the user hasn't reached yet on a new form) doesn't get flagged just for being blurred past.
// Once a field has had content, live validation keeps running even if it's cleared again, so
// a required field emptied out after typing still reports "Missing ...!" in real time.
export function initLiveSeoValidation() {
    const languages = getSeoLanguages();
    const textFields = ['seo_title', 'seo_page_title', 'seo_description', 'seo_keywords', 'seo_author'];
    const startedFields = new WeakSet();

    for (const lang of languages) {
        for (const field of textFields) {
            const input = getSeoInput(field, lang);
            if (!input) continue;

            if (input.value.trim().length > 0) startedFields.add(input);

            const validate = () => {
                if (input.value.trim().length > 0) startedFields.add(input);
                if (startedFields.has(input)) validateSeoFieldLive(field, lang);
            };
            input.addEventListener('input', debounce(validate, 300));
            input.addEventListener('blur', validate);
        }

        const imageInput = getSeoInput('seo_image', lang);
        if (imageInput) {
            imageInput.addEventListener('change', () => checkAndRenderImage(lang));
        }
    }
}
