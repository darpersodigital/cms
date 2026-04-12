// ===== AI SEO Checker (ES Module, Laravel API version) =====

// Get SEO input field dynamically
export function getSeoInput(field, language) {
    return document.querySelector(`input[name*="${language}[${field}]"]`);
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
    } else {
        return "Missing SEO Image!";
    }
}

// Append SEO results under input
export function appendSeoResults(field, language, message, score) {
    const fieldInput = getSeoInput(field, language);
    if (!fieldInput) return;

    const existingDiv = fieldInput.parentNode.parentNode.parentNode.querySelector('.seo-message-container');
    if (existingDiv) existingDiv.remove();

    const resultDiv = document.createElement('div');
    resultDiv.className = 'seo-message-container';
    resultDiv.innerHTML = score < 8
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
    if (score >= 8) return '#2faa7e';
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
export function fallbackToCustomQualityChecker(content, field) {
    let score = 10, message = "Content is well-sized.";





    if (content?.length > 0 && isGibberish(content)) {
        return { score: 0, message: "Content appears to be gibberish. Please rewrite it." };
    }

    if (field === 'seo_title') {
        if (content?.length == 0) { score = 0; message = "Missing SEO Title!" }
        else if (content.length < 50) { score = 3; message = "Title is too short (50-60 chars)"; }
        else if (content.length > 60) { score = 6; message = "Title is too long"; }
        else { score = 10; }
    } else if (field === 'seo_description') {
        if (content?.length == 0) { score = 0; message = "Missing SEO Description!" }
        else if (content.length < 150) { score = 3; message = "Description is too short"; }
        else if (content.length > 160) { score = 6; message = "Description is too long"; }
        else { score = 10; }
    } else if (field === 'seo_keywords') {
        const keywords = content.split(',').map(k => k.trim());
        if (content?.length == 0) { score = 0; message = "Missing SEO Keywords!" }
        else if (keywords.length < 5) { score = 3; message = "Too few keywords (min 5)"; }
        else if (keywords.length > 10) { score = 6; message = "Too many keywords (max 10)"; }
        else { score = 10; }
    } else if (field === 'seo_author' && content) {
        if (content?.length == 0) { score = 0; message = "Missing SEO Author!" }
        else if (content.length < 3) { score = 3; message = "Author name is too short"; }
        else if (content.length > 50) { score = 6; message = "Author name is too long"; }
        else { score = 10; }
    } else {
        if (content.length < 50) { score = 3; message = "Content is too short. Consider at least 50 characters."; }
        else if (content.length > 100) { score = 6; message = "Content is too long. Consider shortening."; }
        else { score = 10; }
    }

    return { score, message };
}

// Collect all fields
export function collectFields(language) {
    return {
        seo_title: getSeoInput('seo_title', language)?.value || "",
        seo_description: getSeoInput('seo_description', language)?.value || "",
        seo_keywords: getSeoInput('seo_keywords', language)?.value || "",
        seo_author: getSeoInput('seo_author', language)?.value || "",
        seo_robots: getSeoInput('seo_robots', language)?.value || "",
        seo_image: getSeoInput('seo_image', language)?.value || ""
    };
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
    const languages = ["en", "ar"];
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
                    aiResultsAllLanguages[lang][field] = fallbackToCustomQualityChecker(fields[field], field);
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
        const imageMessage = checkImage(lang);
        const imageScore = (imageMessage === "Image tag is set and valid!" || imageMessage === "Image file is selected and valid!") ? 10 : 0;
        appendSeoResults('seo_image', lang, imageMessage, imageScore);

        // Custom checks for seo_robots
        const robotsInput = getSeoInput('seo_robots', lang);
        if (robotsInput) {
            const robotsValue = robotsInput.value;
            const robotsMessage = robotsValue ? "Robots field is set" : "Robots field is empty";
            const robotsScore = robotsValue ? 10 : 0;
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