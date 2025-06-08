    </main>

    <footer class="footer">
        <style>
            .footer {
                background-color: var(--darker-bg);
                padding: 30px 0 20px;
                border-top: 1px solid #333;
                margin-top: 40px;
            }

            .footer-content {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 30px;
                margin-bottom: 30px;
            }

            .footer-logo img {
                height: 40px;
            }

            .footer-links h3, .footer-social h3 {
                color: var(--accent-green);
                margin-bottom: 15px;
                font-size: 1.2rem;
            }

            .footer-links ul {
                list-style: none;
            }

            .footer-links li {
                margin-bottom: 10px;
            }

            .footer-links a:hover {
                color: var(--accent-green);
            }

            .social-icons {
                display: flex;
                gap: 15px;
            }

            .social-icons a {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background-color: rgba(76, 175, 80, 0.1);
                color: var(--text-primary);
                font-size: 1.2rem;
                transition: var(--transition);
            }

            .social-icons a:hover {
                background-color: var(--accent-green);
                color: white;
                transform: translateY(-3px);
            }

            .footer-copyright {
                text-align: center;
                padding-top: 20px;
                border-top: 1px solid #333;
                color: var(--text-secondary);
                font-size: 0.9rem;
            }

            @media (max-width: 768px) {
                .footer-content {
                    flex-direction: column;
                    gap: 20px;
                }
            }
        </style>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="<?= SITE_NAME ?>">
                </div>
                <div class="footer-links">
                    <h3>Information</h3>
                    <ul>
                        <li><a href="/about.php">About us</a></li>
                        <li><a href="/rules.php">Rules</a></li>
                        <li><a href="/contact.php">Contacts</a></li>
                    </ul>
                </div>
                <div class="footer-social">
                    <h3>We are on social</h3>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-copyright">
                &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Мобильное меню
            const menuToggle = document.querySelector('.menu-toggle');
            const menuClose = document.querySelector('.menu-close');
            const mobileMenu = document.querySelector('.mobile-menu');
            const overlay = document.querySelector('.overlay');

            menuToggle.addEventListener('click', () => {
                mobileMenu.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });

            menuClose.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            });

            overlay.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            });

            // Голосование
            document.querySelectorAll('.vote-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const postId = this.dataset.postId;
                    const voteType = this.dataset.type;
                    // Здесь будет AJAX запрос для голосования
                    console.log(`Vote ${voteType} for post ${postId}`);
                    
                    // Временная визуализация
                    if (voteType === 'up') {
                        this.classList.toggle('active');
                        this.nextElementSibling.textContent = 
                            parseInt(this.nextElementSibling.textContent) + (this.classList.contains('active') ? 1 : -1);
                    } else {
                        this.classList.toggle('active');
                        this.previousElementSibling.textContent = 
                            parseInt(this.previousElementSibling.textContent) + (this.classList.contains('active') ? -1 : 1);
                    }
                });
            });

            // Автопрокрутка карусели на мобильных устройствах
            const carousel = document.querySelector('.stories-carousel');
            if (carousel && window.innerWidth < 768) {
                let scrollAmount = 0;
                const scrollStep = 135; // ширина элемента + отступ
                
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
            
            // Пауза анимации прогресс-бара при наведении
            const storyItems = document.querySelectorAll('.story-item');
            storyItems.forEach(item => {
                item.addEventListener('mouseenter', () => {
                    if (item.style.getPropertyValue) {
                        item.style.setProperty('--play-state', 'paused');
                    }
                });
                item.addEventListener('mouseleave', () => {
                    if (item.style.getPropertyValue) {
                        item.style.setProperty('--play-state', 'running');
                    }
                });
            });
        });
    </script>
    <script>
function showEditForm(userId) {
    var form = document.getElementById('editForm' + userId);
    form.style.display = "table-row";
}

function hideEditForm(userId) {
    var form = document.getElementById('editForm' + userId);
    form.style.display = "none";
}
</script>
</body>
</html>