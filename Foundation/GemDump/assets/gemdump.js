function highlightSearch() {
    const dumps = document.querySelectorAll('.gemdump');
    const search = document.querySelector('input[x-model="search"]').value.trim();

    dumps.forEach((dump) => {
        dump.innerHTML = dump.textContent;

        if (!search) return;

        const regex = new RegExp(search, 'gi');
        dump.innerHTML = dump.innerHTML.replace(regex, (match) =>
            `<mark class="hl-search-match">${match}</mark>`
        );
    });
}

function nextMatch() {
    const matches = document.querySelectorAll('.hl-search-match');
    if (matches.length === 0) return;
    window.__hlIndex = (window.__hlIndex ?? -1) + 1;
    if (window.__hlIndex >= matches.length) window.__hlIndex = 0;
    matches.forEach(m => m.classList.remove('active'));
    matches[window.__hlIndex].scrollIntoView({behavior: 'smooth', block: 'center'});
    matches[window.__hlIndex].classList.add('active');
}

function prevMatch() {
    const matches = document.querySelectorAll('.hl-search-match');
    if (matches.length === 0) return;
    window.__hlIndex = (window.__hlIndex ?? matches.length) - 1;
    if (window.__hlIndex < 0) window.__hlIndex = matches.length - 1;
    matches.forEach(m => m.classList.remove('active'));
    matches[window.__hlIndex].scrollIntoView({behavior: 'smooth', block: 'center'});
    matches[window.__hlIndex].classList.add('active');
}

window.highlightSearch = highlightSearch;
window.nextMatch = nextMatch;
window.prevMatch = prevMatch;
