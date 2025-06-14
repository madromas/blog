document.addEventListener('DOMContentLoaded', function() {
    // Автопрокрутка карусели на мобильных устройствах
    const carousel = document.querySelector('.stories-carousel');
    if (carousel && window.innerWidth < 768) {
        let scrollAmount = 0;
        const scrollStep = 90; // ширина элемента + отступ
        
        setInterval(() => {
            scrollAmount += scrollStep;
            if (scrollAmount >= carousel.scrollWidth - carousel.clientWidth) {
                scrollAmount = 0;
            }
            carousel.scrollTo({
                left: scrollAmount,
                behavior: 'smooth'
            });
        }, 5000); // 5 секунд
    }
    
    // Обработка голосов
    document.querySelectorAll('.vote-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const voteType = this.dataset.type;
            const voteCount = this.parentElement.querySelector('.vote-count');
            
            fetch(`vote.php?post_id=${postId}&type=${voteType}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        voteCount.textContent = data.newCount;
                        
                        // Визуальный фидбэк
                        if (voteType === 'up') {
                            this.classList.add('voted');
                            this.parentElement.querySelector('.downvote').classList.remove('voted');
                        } else {
                            this.classList.add('voted');
                            this.parentElement.querySelector('.upvote').classList.remove('voted');
                        }
                    }
                });
        });
    });
    
    // Анимация при наведении на истории
    const storyItems = document.querySelectorAll('.story-item');
    storyItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            item.style.transform = 'translateY(-5px)';
            item.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.3)';
        });
        item.addEventListener('mouseleave', () => {
            item.style.transform = '';
            item.style.boxShadow = '';
        });
    });
    
    // Плавное появление элементов при загрузке
    const animateOnScroll = () => {
        const elements = document.querySelectorAll('.post, .sidebar-widget, .story-item');
        
        elements.forEach(el => {
            const elTop = el.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            if (elTop < windowHeight - 100) {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }
        });
    };
    
    // Инициализация анимации
    document.querySelectorAll('.post, .sidebar-widget, .story-item').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    });
    
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Запустить сразу для видимых элементов
});

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const menuClose = document.querySelector('.menu-close');
    const mobileMenu = document.querySelector('.mobile-menu');
    const mobileOverlay = document.createElement('div');
    
    mobileOverlay.className = 'mobile-overlay';
    document.body.appendChild(mobileOverlay);
    
    menuToggle.addEventListener('click', function() {
        mobileMenu.classList.add('show');
        mobileOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    });
    
    menuClose.addEventListener('click', function() {
        mobileMenu.classList.remove('show');
        mobileOverlay.classList.remove('show');
        document.body.style.overflow = '';
    });
    
    mobileOverlay.addEventListener('click', function() {
        mobileMenu.classList.remove('show');
        mobileOverlay.classList.remove('show');
        document.body.style.overflow = '';
    });
    
    // Закрытие меню при клике на ссылку
    document.querySelectorAll('.mobile-nav a').forEach(link => {
        link.addEventListener('click', function() {
            mobileMenu.classList.remove('show');
            mobileOverlay.classList.remove('show');
            document.body.style.overflow = '';
        });
    });
});

if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/service-worker.js')
      .then(function(registration) {
        console.log('ServiceWorker registration successful with scope: ', registration.scope);
      }, function(err) {
        console.log('ServiceWorker registration failed: ', err);
      });
  });
}