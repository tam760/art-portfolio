// Mobile menu
(function(){
    const menuBtn = document.getElementById('menuBtn');
    const closeMenuBtn = document.getElementById('closeMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const menuOverlay = document.getElementById('menuOverlay');
    if (!menuBtn) return;

    function openMenu(){ mobileMenu.classList.add('open'); menuOverlay.classList.remove('hidden'); document.body.style.overflow='hidden'; }
    function closeMenu(){ mobileMenu.classList.remove('open'); menuOverlay.classList.add('hidden'); document.body.style.overflow=''; }

    menuBtn.addEventListener('click', openMenu);
    if (closeMenuBtn) closeMenuBtn.addEventListener('click', closeMenu);
    if (menuOverlay) menuOverlay.addEventListener('click', closeMenu);
    mobileMenu.querySelectorAll('a').forEach(a=>a.addEventListener('click', closeMenu));
})();

// Portfolio filter
(function(){
    const filterBtns = document.querySelectorAll('.filter-btn');
    const portfolioCards = document.querySelectorAll('.portfolio-card');
    if (!filterBtns.length) return;
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b=>b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.dataset.filter;
            portfolioCards.forEach(card => {
                if (filter === 'all' || card.dataset.category === filter) {
                    card.style.display = '';
                    setTimeout(()=>{ card.style.opacity = '1'; card.style.transform = 'scale(1)'; }, 10);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(()=>{ card.style.display = 'none'; }, 300);
                }
            });
        });
    });
})();

// Navbar scroll + active section
(function(){
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        if (navbar) {
            if (window.pageYOffset > 100) navbar.classList.add('shadow-sm');
            else navbar.classList.remove('shadow-sm');
        }
    });

    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    if (!sections.length) return;
    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(sec => {
            if (window.pageYOffset >= sec.offsetTop - 200) current = sec.getAttribute('id');
        });
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) link.classList.add('active');
        });
    });
})();

// Reveal animation
(function(){
    const els = document.querySelectorAll('.reveal');
    if (!els.length || !('IntersectionObserver' in window)) {
        els.forEach(el=>el.classList.add('visible'));
        return;
    }
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
    els.forEach(el => io.observe(el));
})();

// Smooth scroll for anchor
(function(){
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', function(e){
            const href = this.getAttribute('href');
            if (href.length > 1) {
                const target = document.querySelector(href);
                if (target) { e.preventDefault(); target.scrollIntoView({behavior:'smooth', block:'start'}); }
            }
        });
    });
})();

// Contact form ajax
(function(){
    const form = document.querySelector('#contactForm');
    if (!form) return;
    form.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = form.querySelector('button[type=submit]');
        btn.disabled = true;
        btn.textContent = '发送中...';
        try {
            const data = new FormData(form);
            const res = await fetch(form.action, { method: 'POST', body: data, headers: {'X-Requested-With':'XMLHttpRequest'} });
            const json = await res.json();
            const box = document.getElementById('contactResult');
            if (box) {
                box.className = 'badge-msg ' + (json.ok ? 'badge-success' : 'badge-error');
                box.textContent = json.msg;
            }
            if (json.ok) form.reset();
        } catch(err) {
            const box = document.getElementById('contactResult');
            if (box) { box.className = 'badge-msg badge-error'; box.textContent = '网络错误，请稍后重试'; }
        } finally {
            btn.disabled = false;
            btn.textContent = '发送消息';
        }
    });
})();
