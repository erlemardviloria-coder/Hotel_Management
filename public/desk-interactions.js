const moveIn = document.querySelector('form[action="/admit"]');
if (moveIn) {
    function showRent() {
        const option = moveIn.elements.room.selectedOptions[0];
        if (!option) return;
        const base = Number(option.dataset.rate);
        const rent = moveIn.elements.plan.value === 'scholar' ? Math.floor((base * 9 + 5) / 10) : base;
        document.querySelector('#estimate').textContent = 'Monthly rent · ₱' + rent.toLocaleString('en-PH');
    }
    moveIn.addEventListener('change', showRent);
    showRent();
}
let collapsed = [];
window.addEventListener('beforeprint', () => {
    collapsed = Array.from(document.querySelectorAll('details:not([open])'));
    collapsed.forEach(panel => panel.open = true);
});
window.addEventListener('afterprint', () => collapsed.forEach(panel => panel.open = false));
