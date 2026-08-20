/* ВИЗИТ ДОНЕЦК · страница места */
const $ = s => document.querySelector(s);

let toastT;
function toast(m) {
    const t = $('#toast');
    t.textContent = m;
    t.classList.add('show');
    clearTimeout(toastT);
    toastT = setTimeout(() => t.classList.remove('show'), 2600);
}

/* reveal */
const io = new IntersectionObserver(es => es.forEach(e => {
    if (e.isIntersecting) { e.target.classList.add('on'); io.unobserve(e.target); }
}), { threshold: .1 });
document.querySelectorAll('.reveal').forEach(el => io.observe(el));

/* галерея */
window.setMain = (img) => {
    $('#plMain').src = img.src;
    document.querySelectorAll('.pl-thumbs img').forEach(i => i.classList.toggle('on', i === img));
};

/* отзыв → API */
$('#reviewForm').addEventListener('submit', async e => {
    e.preventDefault();
    const f = e.target;
    const btn = f.querySelector('button[type="submit"]');
    btn.disabled = true;

    try {
        const r = await fetch(`/api/v1/places/${f.dataset.slug}/reviews`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({
                author_name: f.author_name.value.trim() || null,
                rating: +f.rating.value,
                text: f.text.value.trim(),
            }),
        });
        const data = await r.json().catch(() => ({}));
        if (r.ok) {
            toast('Спасибо! Отзыв появится после модерации.');
            f.reset();
        } else {
            toast(data.errors ? Object.values(data.errors)[0][0] : (data.message ?? 'Ошибка'));
        }
    } catch (err) {
        toast('Сеть недоступна, попробуйте ещё раз');
    } finally {
        btn.disabled = false;
    }
});
