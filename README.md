# Go Rental - Car Rental Platform

A modern, responsive car rental website built with HTML, CSS, and JavaScript. This platform provides a seamless experience for customers to browse, book, and rent vehicles with ease.

## 📋 Features

- **Vehicle Catalog** - Browse and filter available cars
- **Booking System** - Easy-to-use reservation interface
- **Shop Integration** - E-commerce functionality for vehicle purchases
- **Blog Section** - Latest news and updates about rentals
- **Gallery** - Showcase of available vehicles
- **Responsive Design** - Mobile-friendly interface
- **Contact & Support** - FAQ and contact forms
- **User Account** - Customer account management
- **Dark Mode** - Theme switching capability

## 📁 Project Structure

```
gorental/
├── index.html              # Homepage
├── index-two.html          # Alternative homepage layout
├── index-three.html        # Homepage variant 3
├── index-four.html         # Homepage variant 4
├── fleet.html              # Vehicle fleet listing
├── fleet-single-*.html     # Individual vehicle pages
├── shop.html               # Shop landing page
├── shop-details.html       # Product details
├── cart.html               # Shopping cart
├── checkout.html           # Checkout process
├── blog.html               # Blog listing
├── blog-details.html       # Blog post
├── gallery.html            # Image gallery
├── contact.html            # Contact page
├── faq.html                # FAQ page
├── account.html            # User account
├── about.html              # About page
├── driver.html             # Driver information
├── pricing.html            # Pricing details
│
├── assets/
│   ├── css/                # Stylesheets
│   │   ├── plugins/        # CSS plugins (Bootstrap, FontAwesome, etc.)
│   │   └── vendor/         # Third-party CSS
│   ├── js/                 # JavaScript files
│   │   ├── plugins/        # JS plugins (jQuery, GSAP, Swiper, etc.)
│   │   ├── vendor/         # Third-party JS
│   │   └── main.js         # Main script file
│   ├── images/             # Image assets
│   └── fonts/              # Custom fonts
│
└── partials/               # Reusable HTML components
    ├── header/             # Header variations
    ├── footer/             # Footer variations
    ├── elements/           # Common elements (breadcrumb, sidebar)
    ├── loader.html         # Loading indicator
    ├── scripts.html        # Script includes
    └── style.html          # Style includes
```

## 🛠️ Technologies Used

- **HTML5** - Semantic markup
- **CSS3** - Including SCSS preprocessing
- **JavaScript (ES6+)** - Interactive features
- **Bootstrap 5** - Responsive framework
- **jQuery** - DOM manipulation
- **GSAP** - Animation library
- **Swiper** - Carousel/slider functionality
- **FontAwesome** - Icon library
- **AOS** - Animate on scroll
- **Jarallax** - Parallax effects

## 📦 Key Plugins & Libraries

- **Metis Menu** - Menu system
- **Isotope** - Filtering and sorting
- **jQuery UI** - UI components
- **Magnifying Popup** - Image zoom
- **Smooth Scroll** - Smooth scrolling
- **Counter Up** - Number animations
- **Hover Reveal** - Hover effects
- **Sal** - Scroll animations

## 🚀 Getting Started

### Prerequisites
- A web browser (Chrome, Firefox, Safari, Edge)
- A local web server or netlify/vercel for deployment

### Installation

1. Clone the repository
```bash
git clone https://github.com/yourusername/gorental.git
cd gorental
```

2. Open in your browser
   - Simply open `index.html` in your browser, or
   - Use a local server:
   ```bash
   # Using Python
   python -m http.server 8000
   
   # Using Node.js (with http-server)
   npx http-server
   ```

3. Visit `http://localhost:8000` in your browser

## 📝 Pages Overview

| Page | Purpose |
|------|---------|
| `index.html` | Main landing page |
| `fleet.html` | List of available vehicles |
| `shop.html` | E-commerce shop |
| `blog.html` | Blog & articles |
| `contact.html` | Contact information |
| `faq.html` | Frequently asked questions |
| `about.html` | Company information |
| `pricing.html` | Rental pricing |
| `account.html` | User account management |

## 🎨 Customization

### Styling
- SCSS files are located in `assets/scss/`
- Modify variables in `assets/scss/default/_variables.scss`
- Add custom styles in `assets/scss/default/_custom.scss`

### Content
- Update HTML files directly
- Use partials from `partials/` folder for consistent headers/footers

### Colors & Branding
- Update color variables in SCSS
- Replace logo images in `assets/images/`

## 📱 Responsive Design

The site is fully responsive and tested on:
- Desktop (1200px and above)
- Tablet (768px - 1199px)
- Mobile (320px - 767px)

## 🔐 Security Considerations

- Validate form inputs on the backend (not implemented in frontend)
- Use HTTPS for production deployment
- Protect sensitive payment information with proper backend encryption

## 📄 License

This project is licensed under the MIT License - see [LICENSE](LICENSE) file for details.

## 👥 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📞 Support

For support, email support@gorental.com or open an issue on GitHub.

## 📈 Roadmap

- [ ] Backend integration with Node.js/Express
- [ ] Database integration (MongoDB/MySQL)
- [ ] User authentication system
- [ ] Payment gateway integration
- [ ] Admin dashboard
- [ ] Email notifications
- [ ] SMS notifications
- [ ] Mobile app (React Native/Flutter)

## 🙏 Acknowledgments

- Bootstrap team for the responsive framework
- jQuery community
- GSAP for powerful animations
- All open-source contributors

---

**Built with ❤️ by the Go Rental Team**
