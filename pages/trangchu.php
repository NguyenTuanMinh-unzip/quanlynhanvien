<?php
session_start();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HPSOFT - Giải pháp phần mềm hàng đầu Hải Phòng</title>
  <!-- Meta tags cho SEO -->
  <meta name="description" content="HPSOFT - Đơn vị cung cấp giải pháp phần mềm hàng đầu tại Hải Phòng. Dịch vụ tư vấn CNTT, thiết kế website, hosting và email doanh nghiệp chất lượng cao.">
  <meta name="keywords" content="hpsoft, phần mềm hải phòng, thiết kế website, giải pháp CNTT, hosting, domain, email doanh nghiệp">
  <meta property="og:title" content="HPSOFT - Giải pháp phần mềm hàng đầu Hải Phòng">
  <meta property="og:description" content="Cung cấp dịch vụ tư vấn CNTT, thiết kế website, hosting và email doanh nghiệp chất lượng cao.">
  <meta property="og:image" content="anh/og-image.jpg">
  <meta property="og:url" content="https://hpsoft.vn">
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Font Google -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  
  <!-- AOS Animation Library -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
  
  <style>
    :root {
      --primary-color: #0891B2;     /* Xanh lam đậm */
      --secondary-color: #38BDF8;   /* Xanh lam nhạt */
      --accent-color: #7C3AED;      /* Tím */
      --gradient-primary: linear-gradient(135deg, #0891B2, #7C3AED);
      --text-dark: #0F172A;         /* Đen xanh */
      --text-light: #64748B;        /* Xám */
      --background-light: #F1F5F9;  /* Nền xanh rất nhạt */
      --white: #FFFFFF;
    }
    body {
      font-family: 'Roboto', sans-serif;
      color: var(--text-dark);
    }

    h1, h2, h3, h4, h5, h6 {
      font-family: 'Montserrat', sans-serif;
    }

    .btn-primary {
      background-color: var(--primary-color);
      transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
      background-color: var(--secondary-color);
      transform: translateY(-2px);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .service-card {
      transition: all 0.3s ease;
    }
    
    .service-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    .service-icon {
      color: var(--primary-color);
      font-size: 3rem;
      transition: all 0.3s ease;
    }

    .service-card:hover .service-icon {
      color: var(--secondary-color);
      transform: scale(1.1);
    }

    .testimonial-card {
      transition: all 0.3s ease;
    }
    
    .testimonial-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }
    
    /* Mobile menu styles */
    .mobile-menu {
      transition: all 0.3s ease;
      transform: translateX(-100%);
    }
    
    .mobile-menu.active {
      transform: translateX(0);
    }
    
    /* Call to action button fixed */
    .cta-fixed {
      position: fixed;
      bottom: 20px;
      right: 30px;
      z-index: 99;
      transition: all 0.3s ease;
    }
    
    .cta-fixed:hover {
      transform: scale(1.05);
    }
    
    /* Scroll indicator */
    .scroll-indicator {
      height: 3px;
      background-color: var(--secondary-color);
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;
      width: 0%;
    }
  </style>
</head>
<body class="bg-gray-50">
  <!-- Scroll indicator -->
  <div class="scroll-indicator" id="scrollIndicator"></div>

  <!-- Fixed CTA Button -->
  <div class="cta-fixed">
    <a href="#contact" class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-full shadow-lg">
      <i class="fas fa-phone-alt mr-2"></i>
      <span class="hidden md:inline">Liên hệ ngay</span>
    </a>
  </div>

  <!-- Header & Navigation -->
  <header class="bg-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-4 py-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <img src="anh/images.png" alt="Logo HPSOFT" class="h-16 md:h-20">
        </div>
        
        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-8">
          <a href="#home" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">Trang chủ</a>
          <a href="#about" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">Giới thiệu</a>
          <a href="#services" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">Dịch vụ</a>
          <a href="#testimonials" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">Đánh giá</a>
          <a href="#partners" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">Đối tác</a>
          <a href="#blog" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">Blog</a>
          <a href="#contact" class="text-gray-700 hover:text-blue-600 font-medium transition duration-300">Liên hệ</a>
          
          <?php if (!isset($_SESSION['user'])): ?>
            <a href="login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition duration-300">
              Đăng nhập
            </a>
          <?php else: ?>
            <a href="index.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition duration-300">
              Trang quản trị
            </a>
          <?php endif; ?>
        </nav>
        
        <!-- Mobile menu button -->
        <div class="md:hidden">
          <button id="menuBtn" class="text-gray-700 focus:outline-none">
            <i class="fas fa-bars text-2xl"></i>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Mobile Navigation Menu -->
    <div id="mobileMenu" class="mobile-menu fixed inset-0 bg-white z-50 flex flex-col justify-start pt-20 px-6 md:hidden">
      <button id="closeMenuBtn" class="absolute top-5 right-5 text-gray-700 focus:outline-none">
        <i class="fas fa-times text-2xl"></i>
      </button>
      <div class="flex flex-col space-y-6">
        <a href="#home" class="text-xl text-gray-700 hover:text-blue-600 font-medium transition duration-300">Trang chủ</a>
        <a href="#about" class="text-xl text-gray-700 hover:text-blue-600 font-medium transition duration-300">Giới thiệu</a>
        <a href="#services" class="text-xl text-gray-700 hover:text-blue-600 font-medium transition duration-300">Dịch vụ</a>
        <a href="#testimonials" class="text-xl text-gray-700 hover:text-blue-600 font-medium transition duration-300">Đánh giá</a>
        <a href="#partners" class="text-xl text-gray-700 hover:text-blue-600 font-medium transition duration-300">Đối tác</a>
        <a href="#blog" class="text-xl text-gray-700 hover:text-blue-600 font-medium transition duration-300">Blog</a>
        <a href="#contact" class="text-xl text-gray-700 hover:text-blue-600 font-medium transition duration-300">Liên hệ</a>
        
        <?php if (!isset($_SESSION['user'])): ?>
          <a href="login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded text-center font-medium transition duration-300">
            Đăng nhập
          </a>
        <?php else: ?>
          <a href="index.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded text-center font-medium transition duration-300">
            Trang quản trị
          </a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section id="home" class="relative h-screen">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('anh/photo-1517245386807-bb43f82c33c4.avif');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-blue-900/80 to-black/70"></div>
    <div class="relative z-10 flex items-center justify-center h-full">
      <div class="text-center text-white px-4" data-aos="fade-up" data-aos-delay="200">
        <h1 class="text-4xl md:text-6xl font-bold leading-tight">Chào mừng đến với <span class="text-blue-300">HPSOFT</span></h1>
        <p class="mt-4 text-lg md:text-2xl max-w-2xl mx-auto">Giải pháp công nghệ thông tin hàng đầu tại Hải Phòng, nâng tầm doanh nghiệp của bạn trong kỷ nguyên số</p>
        <div class="mt-8 flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
          <a href="#services" class="btn-primary px-6 py-3 rounded-full text-lg font-medium text-white">
            Khám phá dịch vụ
          </a>
          <a href="#contact" class="bg-transparent border-2 border-white hover:bg-white hover:text-blue-600 text-white px-6 py-3 rounded-full text-lg font-medium transition duration-300">
            Liên hệ ngay
          </a>
        </div>
      </div>
    </div>
    <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 text-white text-center">
      <p class="mb-2 text-sm">Cuộn xuống để xem thêm</p>
      <i class="fas fa-chevron-down animate-bounce text-2xl"></i>
    </div>
  </section>
  
  <!-- Footer -->
  <footer class="bg-gradient-to-r from-blue-600 to-cyan-500 text-white py-16">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
        <div>
          <img src="anh/images.png" alt="Logo HPSOFT" class="h-20 bg-white p-2 rounded mb-4">
          <p class="mb-4">HPSOFT - Giải pháp phần mềm hàng đầu tại Hải Phòng. Chuyên cung cấp dịch vụ tư vấn CNTT, thiết kế website, và hosting chất lượng cao.</p>
          <div class="flex space-x-4">
            <a href="#" class="text-white hover:text-blue-200 transition duration-300">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="text-white hover:text-blue-200 transition duration-300">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="text-white hover:text-blue-200 transition duration-300">
              <i class="fab fa-linkedin-in"></i>
            </a>
            <a href="#" class="text-white hover:text-blue-200 transition duration-300">
              <i class="fab fa-youtube"></i>
            </a>
          </div>
        </div>
        
        <div>
          <h3 class="text-xl font-bold mb-6">Liên kết nhanh</h3>
          <ul class="space-y-3">
            <li><a href="#home" class="text-blue-100 hover:text-white transition duration-300">Trang chủ</a></li>
            <li><a href="#about" class="text-blue-100 hover:text-white transition duration-300">Giới thiệu</a></li>
            <li><a href="#services" class="text-blue-100 hover:text-white transition duration-300">Dịch vụ</a></li>
            <li><a href="#testimonials" class="text-blue-100 hover:text-white transition duration-300">Đánh giá</a></li>
            <li><a href="#partners" class="text-blue-100 hover:text-white transition duration-300">Đối tác</a></li>
            <li><a href="#blog" class="text-blue-100 hover:text-white transition duration-300">Blog</a></li>
            <li><a href="#contact" class="text-blue-100 hover:text-white transition duration-300">Liên hệ</a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="text-xl font-bold mb-6">Dịch vụ</h3>
          <ul class="space-y-3">
            <li><a href="#" class="text-blue-100 hover:text-white transition duration-300">Tư vấn và giải pháp CNTT</a></li>
            <li><a href="#" class="text-blue-100 hover:text-white transition duration-300">Thiết kế website</a></li>
            <li><a href="#" class="text-blue-100 hover:text-white transition duration-300">Hosting & Email</a></li>
            <li><a href="#" class="text-blue-100 hover:text-white transition duration-300">Phát triển ứng dụng di động</a></li>
            <li><a href="#" class="text-blue-100 hover:text-white transition duration-300">SEO & Marketing</a></li>
            <li><a href="#" class="text-blue-100 hover:text-white transition duration-300">Bảo mật thông tin</a></li>
          </ul>
        </div>
        
        <div>
          <h3 class="text-xl font-bold mb-6">Thông tin liên hệ</h3>
          <ul class="space-y-3">
            <li class="flex items-start">
              <i class="fas fa-map-marker-alt mt-1 mr-3"></i>
              <span>Paris 12-04 Vinhomes Imperia, Thượng Lý, Hồng Bàng, Hải Phòng</span>
            </li>
            <li class="flex items-start">
              <i class="fas fa-phone-alt mt-1 mr-3"></i>
              <span>+84 34323200</span>
            </li>
            <li class="flex items-start">
              <i class="fas fa-envelope mt-1 mr-3"></i>
              <span>info@hpsoft.vn</span>
            </li>
            <li class="flex items-start">
              <i class="fas fa-clock mt-1 mr-3"></i>
              <span>Thứ 2 - Thứ 6: 8:00 - 17:30<br>Thứ 7: 8:00 - 12:00</span>
            </li>
          </ul>
        </div>
      </div>
      
      <div class="border-t-2 border-white-500 mt-10 pt-8 text-center">
        <p>&copy; 2025 HPSOFT. All rights reserved.</p>
        <p class="mt-2 text-sm">Giải pháp phần mềm - Nâng cao hiệu suất, tối ưu marketing.</p>
      </div>
    </div>
  </footer>

  <!-- Back to top button -->
  <button id="backToTop" class="fixed bottom-20 right-10 bg-blue-600 text-white w-12 h-12 rounded-full flex items-center justify-center shadow-lg opacity-0 invisible transition-all duration-300">
    <i class="fas fa-chevron-up"></i>
  </button>

  <!-- AOS Animation Script -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
  
  <!-- Custom JavaScript -->
  <script>
    // Initialize AOS Animation
    AOS.init({
      duration: 800,
      offset: 100,
      once: true
    });
    
    // Mobile menu functionality
    document.getElementById('menuBtn').addEventListener('click', function() {
      document.getElementById('mobileMenu').classList.add('active');
    });
    
    document.getElementById('closeMenuBtn').addEventListener('click', function() {
      document.getElementById('mobileMenu').classList.remove('active');
    });
    
    // Close mobile menu when clicking on a link
    const mobileLinks = document.querySelectorAll('#mobileMenu a');
    mobileLinks.forEach(link => {
      link.addEventListener('click', function() {
        document.getElementById('mobileMenu').classList.remove('active');
      });
    });
    
    // Scroll indicator
    window.addEventListener('scroll', function() {
      const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
      const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      const scrolled = (winScroll / height) * 100;
      document.getElementById('scrollIndicator').style.width = scrolled + '%';
      
      // Back to top button visibility
      const backToTopBtn = document.getElementById('backToTop');
      if (winScroll > 300) {
        backToTopBtn.classList.remove('opacity-0', 'invisible');
        backToTopBtn.classList.add('opacity-100', 'visible');
      } else {
        backToTopBtn.classList.remove('opacity-100', 'visible');
        backToTopBtn.classList.add('opacity-0', 'invisible');
      }
    });
    
    // Back to top functionality
    document.getElementById('backToTop').addEventListener('click', function() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          window.scrollTo({
            top: targetElement.offsetTop - 80,
            behavior: 'smooth'
          });
        }
      });
    });
  </script>

</body>
</html>
  
  <!-- Blog Section -->
  <section id="blog" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16" data-aos="fade-up">
        <h2 class="text-3xl md:text-4xl font-bold text-blue-500 mb-4">Blog & Tin tức</h2>
        <div class="w-20 h-1 bg-blue-500 mx-auto mb-6"></div>
        <p class="max-w-2xl mx-auto text-gray-600 text-lg">
          Cập nhật thông tin mới nhất về công nghệ và giải pháp CNTT
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Blog Item 1 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden" data-aos="fade-up" data-aos-delay="100">
        <div class="h-48 bg-cover bg-center" style="background-image: url('anh/xuhuong2025.jpg');"></div>
          <div class="p-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
              <i class="far fa-calendar-alt mr-2"></i>
              <span>25/03/2025</span>
              <span class="mx-2">•</span>
              <i class="far fa-user mr-2"></i>
              <span>Admin</span>
            </div>
            <h3 class="text-xl font-bold text-blue-600 mb-3">10 xu hướng công nghệ thông tin nổi bật năm 2025</h3>
            <p class="text-gray-700 mb-4">
              Khám phá những xu hướng công nghệ đang định hình tương lai của doanh nghiệp trong kỷ nguyên số.
            </p>
            <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
              Đọc tiếp <i class="fas fa-arrow-right ml-2"></i>
            </a>
          </div>
        </div>
        
        <!-- Blog Item 2 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden" data-aos="fade-up" data-aos-delay="200">
        <div class="h-48 bg-cover bg-center" style="background-image: url('anh/chuyendoiso.jpg');"></div>
          <div class="p-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
              <i class="far fa-calendar-alt mr-2"></i>
              <span>18/03/2025</span>
              <span class="mx-2">•</span>
              <i class="far fa-user mr-2"></i>
              <span>Admin</span>
            </div>
            <h3 class="text-xl font-bold text-blue-600 mb-3">5 lý do doanh nghiệp nên chuyển đổi số ngay lập tức</h3>
            <p class="text-gray-700 mb-4">
              Tìm hiểu tại sao chuyển đổi số không còn là lựa chọn mà là điều bắt buộc đối với doanh nghiệp hiện đại.
            </p>
            <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
              Đọc tiếp <i class="fas fa-arrow-right ml-2"></i>
            </a>
          </div>
        </div>
        
        <!-- Blog Item 3 -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden" data-aos="fade-up" data-aos-delay="300">
          <div class="h-48 bg-cover bg-center" style="background-image: url('anh/baomat.jpg');"></div>
          <div class="p-6">
            <div class="flex items-center text-sm text-gray-500 mb-2">
              <i class="far fa-calendar-alt mr-2"></i>
              <span>10/03/2025</span>
              <span class="mx-2">•</span>
              <i class="far fa-user mr-2"></i>
              <span>Admin</span>
            </div>
            <h3 class="text-xl font-bold text-blue-600 mb-3">Bảo mật dữ liệu: Thách thức lớn nhất của doanh nghiệp</h3>
            <p class="text-gray-700 mb-4">
              Giải pháp toàn diện giúp doanh nghiệp bảo vệ dữ liệu quan trọng trước các cuộc tấn công mạng.
            </p>
            <a href="#" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
              Đọc tiếp <i class="fas fa-arrow-right ml-2"></i>
            </a>
          </div>
        </div>
      </div>
      
      <div class="text-center mt-12">
        <a href="#" class="btn-primary inline-block px-6 py-3 rounded-full text-white font-medium">
          Xem tất cả bài viết <i class="fas fa-arrow-right ml-2"></i>
        </a>
      </div>
    </div>
  </section>
  
  <!-- Contact Section -->
  <section id="contact" class="py-20 bg-white">
    <div class="container mx-auto px-4">
      <div class="flex flex-col md:flex-row">
        <div class="w-full md:w-1/2 mb-10 md:mb-0" data-aos="fade-right">
          <h2 class="text-3xl md:text-4xl font-bold text-blue-500 mb-4">Liên hệ với chúng tôi</h2>
          <div class="w-20 h-1 bg-blue-600 mb-6"></div>
          <p class="text-gray-700 leading-relaxed mb-8">
            Hãy để lại thông tin, chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất. Hoặc bạn có thể liên hệ trực tiếp qua các kênh dưới đây:
          </p>
          
          <div class="space-y-6">
            <div class="flex items-start">
              <div class="bg-blue-100 rounded-full p-3 mr-4">
                <i class="fas fa-map-marker-alt text-blue-600"></i>
              </div>
              <div>
                <h3 class="font-bold text-gray-800 mb-1">Địa chỉ</h3>
                <p class="text-gray-600">Paris 12-04 Vinhomes Imperia, Thượng Lý, Hồng Bàng, Hải Phòng</p>
              </div>
            </div>
            
            <div class="flex items-start">
              <div class="bg-blue-100 rounded-full p-3 mr-4">
                <i class="fas fa-phone-alt text-blue-600"></i>
              </div>
              <div>
                <h3 class="font-bold text-gray-800 mb-1">Điện thoại</h3>
                <p class="text-gray-600">+84 343523200</p>
              </div>
            </div>
            
            <div class="flex items-start">
              <div class="bg-blue-100 rounded-full p-3 mr-4">
                <i class="fas fa-envelope text-blue-600"></i>
              </div>
              <div>
                <h3 class="font-bold text-gray-800 mb-1">Email</h3>
                <p class="text-gray-600">info@hpsoft.vn</p>
              </div>
            </div>
            
            <div class="flex items-start">
              <div class="bg-blue-100 rounded-full p-3 mr-4">
                <i class="fas fa-clock text-blue-600"></i>
              </div>
              <div>
                <h3 class="font-bold text-gray-800 mb-1">Giờ làm việc</h3>
                <p class="text-gray-600">Thứ 2 - Thứ 6: 8:00 - 17:30</p>
                <p class="text-gray-600">Thứ 7: 8:00 - 12:00</p>
              </div>
            </div>
          </div>
          
          <div class="mt-8">
            <h3 class="font-bold text-gray-800 mb-3">Theo dõi chúng tôi</h3>
            <div class="flex space-x-4">
              <a href="#" class="bg-blue-100 text-blue-600 hover:bg-blue-200 transition duration-300 rounded-full w-10 h-10 flex items-center justify-center">
                <i class="fab fa-facebook-f"></i>
              </a>
              <a href="#" class="bg-blue-100 text-blue-600 hover:bg-blue-200 transition duration-300 rounded-full w-10 h-10 flex items-center justify-center">
                <i class="fab fa-twitter"></i>
              </a>
              <a href="#" class="bg-blue-100 text-blue-600 hover:bg-blue-200 transition duration-300 rounded-full w-10 h-10 flex items-center justify-center">
                <i class="fab fa-linkedin-in"></i>
              </a>
              <a href="#" class="bg-blue-100 text-blue-600 hover:bg-blue-200 transition duration-300 rounded-full w-10 h-10 flex items-center justify-center">
                <i class="fab fa-youtube"></i>
              </a>
            </div>
          </div>
        </div>
        
        <div class="w-full md:w-1/2 md:pl-10" data-aos="fade-left">
          <div class="bg-white rounded-lg shadow-xl p-8">
            <h3 class="text-2xl font-bold text-blue-500 mb-6">Gửi yêu cầu tư vấn</h3>
            <form action="https://formsubmit.co/minh90504@st.vimaru.edu.vn" method="POST">
              <input type="hidden" name="_next" value="http://localhost/quanlynhanvien/pages/camon.html">
              <div class="mb-6">
                <label for="name" class="block text-gray-700 font-medium mb-2">Họ tên</label>
                <input type="text" id="name" name="Họ tên" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
              </div>
              
              <div class="mb-6">
                <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                <input type="email" id="email" name="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
              </div>
              
              <div class="mb-6">
                <label for="phone" class="block text-gray-700 font-medium mb-2">Số điện thoại</label>
                <input type="tel" id="phone" name="Số điện thoại" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
              </div>
              
              <div class="mb-6">
                <label for="service" class="block text-gray-700 font-medium mb-2">Dịch vụ quan tâm</label>
                <select id="service" name="Dịch vụ" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent">
                  <option value="">-- Chọn dịch vụ --</option>
                  <option value="Tư vấn và giải pháp CNTT">Tư vấn và giải pháp CNTT</option>
                  <option value="Thiết kế website">Thiết kế website</option>
                  <option value="Hosting & Email">Hosting & Email</option>
                  <option value="Phát triển ứng dụng di động">Phát triển ứng dụng di động</option>
                  <option value="SEO & Marketing">SEO & Marketing</option>
                  <option value="Bảo mật thông tin">Bảo mật thông tin</option>
                </select>
              </div>
              
              <div class="mb-6">
                <label for="message" class="block text-gray-700 font-medium mb-2">Nội dung</label>
                <textarea id="message" name="Nội dung yêu cầu" rows="5" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent"></textarea>
              </div>
              
              <button type="submit" class="w-full btn-primary py-3 rounded-lg text-white font-medium transition duration-300">
                Gửi yêu cầu
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="py-20 bg-white">
      <div class="container mx-auto px-4">
          <div class="flex flex-col md:flex-row items-center">
          <div class="w-full md:w-1/2 mb-10 md:mb-0 text-center" data-aos="fade-right">
            <div>
              <img src="anh/images.png" alt="HPSOFT" class="rounded-lg shadow-xl mx-auto max-w-md">
              <div class="mt-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg inline-block">
                <p class="font-bold text-lg">10+ năm kinh nghiệm</p>
              </div>
            </div>
        </div>
        <div class="w-full md:w-1/2 md:pl-12" data-aos="fade-left">
          <h2 class="text-3xl md:text-4xl font-bold text-blue-500 mb-2">Về chúng tôi</h2>
          <div class="w-20 h-1 bg-blue-500 mb-6"></div>
          <h3 class="text-2xl font-semibold text-gray-800 mb-4">Giải pháp CNTT toàn diện cho doanh nghiệp của bạn</h3>
          <p class="text-gray-700 leading-relaxed mb-6">
            HPSOFT là đơn vị tiên phong trong lĩnh vực giải pháp phần mềm tại Hải Phòng. Chúng tôi chuyên cung cấp các dịch vụ tư vấn CNTT, thiết kế website chuẩn SEO, và các giải pháp công nghệ số nhằm nâng cao hiệu suất làm việc và tối ưu hóa quy trình doanh nghiệp.
          </p>
          <p class="text-gray-700 leading-relaxed mb-8">
            Với đội ngũ chuyên gia giàu kinh nghiệm và tâm huyết, HPSOFT cam kết đem lại sản phẩm chất lượng, đáp ứng nhu cầu của khách hàng trong thời đại chuyển đổi số.
          </p>
          <div class="grid grid-cols-2 gap-4 mb-8">
            <div class="flex items-center">
              <i class="fas fa-check-circle text-blue-600 mr-2"></i>
              <span>Chuyên nghiệp</span>
            </div>
            <div class="flex items-center">
              <i class="fas fa-check-circle text-blue-600 mr-2"></i>
              <span>Sáng tạo</span>
            </div>
            <div class="flex items-center">
              <i class="fas fa-check-circle text-blue-600 mr-2"></i>
              <span>Đúng tiến độ</span>
            </div>
            <div class="flex items-center">
              <i class="fas fa-check-circle text-blue-600 mr-2"></i>
              <span>Hỗ trợ 24/7</span>
            </div>
          </div>
          <a href="#contact" class="btn-primary inline-block px-6 py-3 rounded-full text-white font-medium">
            Tìm hiểu thêm <i class="fas fa-arrow-right ml-2"></i>
          </a>
        </div>
      </div>
      
      <div class="text-center mt-12">
        <a href="#testimonials" class="btn-primary inline-block px-6 py-3 rounded-full text-white font-medium">
          Xem thêm đánh giá <i class="fas fa-arrow-right ml-2"></i>
        </a>
      </div>
    </div>
  </section>
  
  <!-- Partners Section -->
  <section id="partners" class="py-20 bg-white">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16" data-aos="fade-up">
        <h2 class="text-3xl md:text-4xl font-bold text-blue-500 mb-4">Đối tác của chúng tôi</h2>
        <div class="w-20 h-1 bg-blue-500 mx-auto mb-6"></div>
        <p class="max-w-2xl mx-auto text-gray-600 text-lg">
          Chúng tôi tự hào được hợp tác với các đối tác hàng đầu trong và ngoài nước
        </p>
      </div>
      
      <div class="flex flex-wrap justify-center items-center gap-8 md:gap-16">
        <div class="w-32 h-32 bg-cover bg-center" justify-center p-4" data-aos="zoom-in" data-aos-delay="100" style="background-image: url('anh/logo1.jpg');"></div>
        <div class="w-32 h-32 bg-cover bg-center" justify-center p-4" data-aos="zoom-in" data-aos-delay="100" style="background-image: url('anh/logo2.jpg');"></div>
        <div class="w-32 h-32 bg-cover bg-center" justify-center p-4" data-aos="zoom-in" data-aos-delay="100" style="background-image: url('anh/logo3.png');"></div>
        <div class="w-32 h-32 bg-cover bg-center" justify-center p-4" data-aos="zoom-in" data-aos-delay="100" style="background-image: url('anh/logo4.png');"></div>
        <div class="w-32 h-32 bg-cover bg-center" justify-center p-4" data-aos="zoom-in" data-aos-delay="100" style="background-image: url('anh/logo5.png');"></div>
        <div class="w-32 h-32 bg-cover bg-center" justify-center p-4" data-aos="zoom-in" data-aos-delay="100" style="background-image: url('anh/logo6.jpg');"></div>
      </div>
    </div>
  </section>

  <!-- Counter Section -->
  <section class="bg-gradient-to-r from-cyan-500 to-blue-600 text-white py-16">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        <div data-aos="fade-up" data-aos-delay="100">
          <i class="fas fa-users text-4xl mb-4"></i>
          <h3 class="text-3xl font-bold mb-2">200+</h3>
          <p>Khách hàng</p>
        </div>
        <div data-aos="fade-up" data-aos-delay="200">
          <i class="fas fa-laptop-code text-4xl mb-4"></i>
          <h3 class="text-3xl font-bold mb-2">350+</h3>
          <p>Dự án hoàn thành</p>
        </div>
        <div data-aos="fade-up" data-aos-delay="300">
          <i class="fas fa-briefcase text-4xl mb-4"></i>
          <h3 class="text-3xl font-bold mb-2">10+</h3>
          <p>Năm kinh nghiệm</p>
        </div>
        <div data-aos="fade-up" data-aos-delay="400">
          <i class="fas fa-trophy text-4xl mb-4"></i>
          <h3 class="text-3xl font-bold mb-2">15+</h3>
          <p>Giải thưởng</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Services Section -->
  <section id="services" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16" data-aos="fade-up">
        <h2 class="text-3xl md:text-4xl font-bold text-blue-500 mb-4">Dịch vụ của chúng tôi</h2>
        <div class="w-20 h-1 bg-blue-500 mx-auto mb-6"></div>
        <p class="max-w-2xl mx-auto text-gray-600 text-lg">
          Cung cấp các giải pháp công nghệ thông tin toàn diện, giúp doanh nghiệp của bạn phát triển vượt bậc
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Service 1 -->
        <div class="service-card bg-white p-8 rounded-lg shadow-md" data-aos="fade-up" data-aos-delay="100">
          <div class="flex justify-center mb-6">
            <i class="fas fa-laptop-code service-icon"></i>
          </div>
          <h3 class="text-xl font-bold text-blue-600 text-center mb-4">Tư vấn và giải pháp CNTT</h3>
          <p class="text-gray-700 text-center mb-6">
            Cung cấp các giải pháp công nghệ thông tin toàn diện, hỗ trợ quá trình chuyển đổi số và nâng cao hiệu quả làm việc cho doanh nghiệp.
          </p>
          <div class="text-center">
            <a href="#contact" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
              Tìm hiểu thêm <i class="fas fa-arrow-right ml-2"></i>
            </a>
          </div>
        </div>
        
        <!-- Service 2 -->
        <div class="service-card bg-white p-8 rounded-lg shadow-md" data-aos="fade-up" data-aos-delay="200">
          <div class="flex justify-center mb-6">
            <i class="fas fa-globe service-icon"></i>
          </div>
          <h3 class="text-xl font-bold text-blue-600 text-center mb-4">Thiết kế website</h3>
          <p class="text-gray-700 text-center mb-6">
            Xây dựng trang web hiện đại, chuẩn SEO, responsive trên mọi thiết bị, giúp quảng bá thương hiệu doanh nghiệp hiệu quả.
          </p>
          <div class="text-center">
            <a href="#contact" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
              Tìm hiểu thêm <i class="fas fa-arrow-right ml-2"></i>
            </a>
          </div>
        </div>
        
        <!-- Service 3 -->
        <div class="service-card bg-white p-8 rounded-lg shadow-md" data-aos="fade-up" data-aos-delay="300">
          <div class="flex justify-center mb-6">
            <i class="fas fa-server service-icon"></i>
          </div>
          <h3 class="text-xl font-bold text-blue-600 text-center mb-4">Hosting, Domain và Email</h3>
          <p class="text-gray-700 text-center mb-6">
            Đăng ký tên miền, cho thuê hosting và cung cấp dịch vụ email doanh nghiệp chuyên nghiệp, đảm bảo sự ổn định và bảo mật.
          </p>
          <div class="text-center">
            <a href="#contact" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
              Tìm hiểu thêm <i class="fas fa-arrow-right ml-2"></i>
            </a>
          </div>
        </div>
        
        <!-- Service 4 -->
        <div class="service-card bg-white p-8 rounded-lg shadow-md" data-aos="fade-up" data-aos-delay="400">
          <div class="flex justify-center mb-6">
            <i class="fas fa-mobile-alt service-icon"></i>
          </div>
          <h3 class="text-xl font-bold text-blue-600 text-center mb-4">Phát triển ứng dụng di động</h3>
          <p class="text-gray-700 text-center mb-6">
            Thiết kế và phát triển ứng dụng di động đa nền tảng, giúp doanh nghiệp tiếp cận khách hàng mọi lúc, mọi nơi.
          </p>
          <div class="text-center">
            <a href="#contact" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
              Tìm hiểu thêm <i class="fas fa-arrow-right ml-2"></i>
            </a>
          </div>
        </div>
        
        <!-- Service 5 -->
        <div class="service-card bg-white p-8 rounded-lg shadow-md" data-aos="fade-up" data-aos-delay="500">
          <div class="flex justify-center mb-6">
            <i class="fas fa-chart-line service-icon"></i>
          </div>
          <h3 class="text-xl font-bold text-blue-600 text-center mb-4">Dịch vụ SEO & Marketing</h3>
          <p class="text-gray-700 text-center mb-6">
            Tối ưu hóa công cụ tìm kiếm và chiến lược marketing số, giúp doanh nghiệp tăng lượng truy cập và chuyển đổi khách hàng.
          </p>
          <div class="text-center">
            <a href="#contact" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
              Tìm hiểu thêm <i class="fas fa-arrow-right ml-2"></i>
            </a>
          </div>
        </div>
        
        <!-- Service 6 -->
        <div class="service-card bg-white p-8 rounded-lg shadow-md" data-aos="fade-up" data-aos-delay="600">
          <div class="flex justify-center mb-6">
            <i class="fas fa-shield-alt service-icon"></i>
          </div>
          <h3 class="text-xl font-bold text-blue-600 text-center mb-4">Bảo mật thông tin</h3>
          <p class="text-gray-700 text-center mb-6">
            Cung cấp giải pháp bảo mật toàn diện, bảo vệ dữ liệu và hệ thống của doanh nghiệp trước các mối đe dọa từ bên ngoài.
          </p>
          <div class="text-center">
            <a href="#contact" class="text-blue-600 hover:text-blue-800 font-medium inline-flex items-center">
              Tìm hiểu thêm <i class="fas fa-arrow-right ml-2"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Demo Video Section -->
  <section class="py-20 bg-white">
    <div class="container mx-auto px-4">
      <div class="flex flex-col md:flex-row items-center">
        <div class="w-full md:w-1/2 mb-10 md:mb-0" data-aos="fade-right">
          <video controls class="rounded-lg shadow-xl aspect-video w-full">
          <source src="anh/videodemo.mp4" type="video/mp4">
            Trình duyệt của bạn không hỗ trợ video.
            </video>
        </div>
        <div class="w-full md:w-1/2 md:pl-12" data-aos="fade-left">
          <h2 class="text-3xl md:text-4xl font-bold text-blue-500 mb-4">Xem demo sản phẩm</h2>
          <div class="w-20 h-1 bg-blue-500 mb-6"></div>
          <p class="text-gray-700 leading-relaxed mb-6">
            Khám phá cách các giải pháp của HPSOFT có thể nâng cao hiệu suất và tối ưu hóa quy trình làm việc của doanh nghiệp bạn. Video demo này sẽ giúp bạn hiểu rõ hơn về cách triển khai và các tính năng của sản phẩm.
          </p>
          <ul class="space-y-3 mb-8">
            <li class="flex items-start">
              <i class="fas fa-check-circle text-blue-600 mt-1 mr-3"></i>
              <span>Giao diện người dùng thân thiện, dễ sử dụng</span>
            </li>
            <li class="flex items-start">
              <i class="fas fa-check-circle text-blue-600 mt-1 mr-3"></i>
              <span>Tính năng quản lý toàn diện, giám sát hiệu quả</span>
            </li>
            <li class="flex items-start">
              <i class="fas fa-check-circle text-blue-600 mt-1 mr-3"></i>
              <span>Khả năng tùy biến theo nhu cầu doanh nghiệp</span>
            </li>
            <li class="flex items-start">
              <i class="fas fa-check-circle text-blue-600 mt-1 mr-3"></i>
              <span>Hỗ trợ đa nền tảng, truy cập mọi lúc, mọi nơi</span>
            </li>
          </ul>
          <a href="#contact" class="btn-primary inline-block px-6 py-3 rounded-full text-white font-medium">
            Đăng ký demo <i class="fas fa-arrow-right ml-2"></i>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials Section -->
  <section id="testimonials" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
      <div class="text-center mb-16" data-aos="fade-up">
        <h2 class="text-3xl md:text-4xl font-bold text-blue-500 mb-4">Khách hàng nói gì về chúng tôi</h2>
        <div class="w-20 h-1 bg-blue-500 mx-auto mb-6"></div>
        <p class="max-w-2xl mx-auto text-gray-600 text-lg">
          Sự hài lòng của khách hàng là thước đo thành công của chúng tôi
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Testimonial 1 -->
        <div class="testimonial-card bg-white p-8 rounded-lg shadow-md" data-aos="fade-up" data-aos-delay="100">
          <div class="flex justify-center mb-6">
            <div class="w-20 h-20 rounded-full overflow-hidden">
              <img src="anh/pro1.jpg" alt="Avatar" class="w-full h-full object-cover">
            </div>
          </div>
          <div class="mb-4">
            <div class="flex justify-center">
              <div class="flex text-yellow-400">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
              </div>
            </div>
          </div>
          <p class="text-gray-700 italic text-center mb-6">
            "HPSOFT đã giúp công ty chúng tôi xây dựng một hệ thống website bán hàng chuyên nghiệp, tối ưu cho SEO và trải nghiệm người dùng. Doanh thu online của chúng tôi đã tăng 150% sau 6 tháng triển khai."
          </p>
          <div class="text-center">
            <h4 class="font-bold text-blue-600">Nguyễn Trường Hưng</h4>
            <p class="text-gray-600 text-sm">Giám đốc - Công ty TNHH Trường Hưng</p>
          </div>
        </div>
        
        <!-- Testimonial 2 -->
        <div class="testimonial-card bg-white p-8 rounded-lg shadow-md" data-aos="fade-up" data-aos-delay="200">
          <div class="flex justify-center mb-6">
            <div class="w-20 h-20 rounded-full overflow-hidden">
              <img src="anh/pro2.png" alt="Avatar" class="w-full h-full object-cover">
            </div>
          </div>
          <div class="mb-4">
            <div class="flex justify-center">
              <div class="flex text-yellow-400">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
              </div>
            </div>
          </div>
          <p class="text-gray-700 italic text-center mb-6">
            "Đội ngũ HPSOFT làm việc cực kỳ chuyên nghiệp và hiểu rõ nhu cầu của khách hàng. Họ đã triển khai giải pháp email doanh nghiệp giúp chúng tôi tăng cường bảo mật và chuyên nghiệp hóa hình ảnh."
          </p>
          <div class="text-center">
            <h4 class="font-bold text-blue-600">Trần Văn Nam</h4>
            <p class="text-gray-600 text-sm">Trưởng phòng IT - Công ty Cường Ngọc</p>
          </div>
        </div>
        
        <!-- Testimonial 3 -->
        <div class="testimonial-card bg-white p-8 rounded-lg shadow-md" data-aos="fade-up" data-aos-delay="300">
          <div class="flex justify-center mb-6">
            <div class="w-20 h-20 rounded-full overflow-hidden">
              <img src="anh/pro3.png" alt="Avatar" class="w-full h-full object-cover">
            </div>
          </div>
          <div class="mb-4">
            <div class="flex justify-center">
              <div class="flex text-yellow-400">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
              </div>
            </div>
          </div>
          <p class="text-gray-700 italic text-center mb-6">
            "Chúng tôi đã hợp tác với HPSOFT trong dự án chuyển đổi số toàn diện. Họ không chỉ cung cấp giải pháp CNTT mà còn tư vấn chiến lược phát triển dài hạn. Kết quả vượt xa mong đợi của chúng tôi."
          </p>
          <div class="text-center">
            <h4 class="font-bold text-blue-600">Lê Minh Chiến</h4>
            <p class="text-gray-600 text-sm">CEO - Công ty CP Phúc Lâm</p>
          </div>
        </div>