/*=== Javascript function indexing hear===========


==================================================*/

(function ($) {
  'use strict';

  var rtsJs = {
    m: function (e) {
      rtsJs.d();
      rtsJs.methods();
    },
    d: function (e) {
      this._window = $(window),
        this._document = $(document),
        this._body = $('body'),
        this._html = $('html')
    },
    methods: function (e) {
      rtsJs.metismenu();
      rtsJs.splitText();
      rtsJs.wowActive();
      rtsJs.swiperActive();
      rtsJs.stickyHeader();
      rtsJs.backToTopInit();
      rtsJs.sideMenu();
      rtsJs.niceSelect();
      rtsJs.vedioActivation();
      rtsJs.videoActive();
      rtsJs.menuCurrentLink();
      rtsJs.preloader();
      rtsJs.counterUp();
      rtsJs.jarallax();
      rtsJs.searchOption();
      rtsJs.mesonaryTab();
      rtsJs.magnificPopupActive();
      rtsJs.datePicker();
      rtsJs.containerResize();
    },
    metismenu: function () {
      $('#mobile-menu-active').metisMenu();
    },
    splitText: function (e) {
      if ($(".rts-slide-anim").length) {
        let animatedTextElements = document.querySelectorAll(".rts-slide-anim");

        animatedTextElements.forEach((element) => {
          if (element.animation) {
            element.animation.progress(1).kill();
            element.split.revert();
          }

          element.split = new SplitText(element, {
            type: "lines,words,chars",
            linesClass: "split-line",
          });
          gsap.set(element, { perspective: 400 });

          gsap.set(element.split.chars, {
            opacity: 0,
            x: "50",
          });

          element.animation = gsap.to(element.split.chars, {
            scrollTrigger: { trigger: element, start: "top 95%" },
            x: "0",
            y: "0",
            rotateX: "0",
            opacity: 1,
            duration: 1,
            ease: Back.easeOut,
            stagger: 0.02,
          });
        });
      }
    },
    jarallax: function (e) {
      $(document).ready(function () {
        // Function to detect if the device is mobile
        function isMobileDevice() {
          return /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        }

        // Initialize jarallax only if it's not a mobile device
        if (!isMobileDevice()) {
          $('.jarallax').jarallax();
        } else {
          console.log('Jarallax skipped on mobile devices');
        }
      });

    },
    wowActive: function () {
      new WOW().init();
    },
    videoActive: function () {
      $(document).on('click', '#myBtn', function () {
        $("#myVideo").toggleClass("show");

        const $btn = $(this);
        const isFullSize = $btn.data("fullSize"); // Check if it's currently full size

        if (isFullSize) {
          $btn.css({
            opacity: "1",
            width: $btn.data("originalWidth"), // Restore original width
            height: $btn.data("originalHeight") // Restore original height
          }).data("fullSize", false);
        } else {
          // Store the original width and height before changing
          $btn.data("originalWidth", $btn.css("width"));
          $btn.data("originalHeight", $btn.css("height"));

          $btn.css({
            opacity: "0",
            width: "100%", // Set to 100% width
            height: "100%" // Set to 100% height
          }).data("fullSize", true);
        }
      });
      $(document).ready(function () {
        var $video = $("#myVideo");
        var $btn = $("#myBtn");

        if ($video.length && $btn.length) {
          $btn.on("click", function () {
            if ($video[0].paused) {
              $video[0].play();
            } else {
              $video[0].pause();
            }
          });
        }
      });

    },

    swiperActive: function (e) {
      $(document).ready(function () {
        var swiper = new Swiper(".rts-banner-slider-active", {
          slidesPerView: 1,
          speed: 1200,
          effect: "fade",
          autoplay: {
            delay: 6000,
            disableOnInteraction: false,
          },
          loop: true,
          pagination: {
            el: ".rts-banner-dots",
            clickable: true,
          },
        });
      });
      $(document).ready(function () {
        var swiper = new Swiper(".rts-banner-image-slider", {
          slidesPerView: 1,
          speed: 1200,
          effect: "fade",
          autoplay: {
            delay: 6000,
            disableOnInteraction: false,
          },
          loop: true,
          pagination: {
            el: ".rts-banner-dots",
            clickable: true,
          },
        });
      });
      $(document).ready(function () {
        var swiper = new Swiper(".rts-banner-slider-active-three", {
          slidesPerView: 1,
          speed: 1200,
          effect: "fade",
          autoplay: {
            delay: 6000,
            disableOnInteraction: false,
          },
          loop: true,
          pagination: {
            el: ".rts-banner-dots",
            clickable: true,
          },
        });
      });
      $(document).ready(function () {
        var swiper = new Swiper(".rts-banner-slider-active-four", {
          slidesPerView: 1,
          speed: 1200,
          effect: "fade",
          autoplay: {
            delay: 6000,
            disableOnInteraction: false,
          },
          loop: true,
          pagination: {
            el: ".rts-banner-dots",
            clickable: true,
          },
        });
      });
      
      $(document).ready(function () {
        var swiper = new Swiper(".category-slider", {
          spaceBetween: 30,
          slidesPerView: 6,
          loop: true,
          speed: 1000,
          autoplay: {
            delay: 3000,
          },
          pagination: {
            el: ".rts-category-pagination",
            clickable: true,
          },
          breakpoints: {
            1500: {
              slidesPerView: 6,
            },
            1440: {
              slidesPerView: 4,
            },
            991: {
              slidesPerView: 3,
            },
            767: {
              slidesPerView: 2,
            },
            575: {
              slidesPerView: 1,
            },
            0: {
              slidesPerView: 1,
            }
          },
        });
      });
      $(document).ready(function () {
        // Check if the sliders exist on the page
        if ($(".rts-testimonials-content-slider").length && $(".rts-testimonials-images-slider").length) {
          var swiper_1 = new Swiper(".rts-testimonials-content-slider", {
            slidesPerView: 1,
            spaceBetween: 0,
            speed: 1800,
            loop: true,
            pagination: {
              el: ".rts-testimonials-dot",
              clickable: true,
            },
          });

          var swiper_2 = new Swiper(".rts-testimonials-images-slider", {
            slidesPerView: 1,
            spaceBetween: 0,
            effect: "fade",
            speed: 1800,
            loop: true,
          });

          // Synchronize the two Swipers
          swiper_1.controller.control = swiper_2;
          swiper_2.controller.control = swiper_1;
        }
      });
      $(document).ready(function () {
        var testiItem = new Swiper(".rts-testimonials-item-slider", {
          slidesPerView: 3,
          spaceBetween: 30,
          loop: true,
          speed: 1500,
          autoplay: {
            delay: 3000,
          },
          pagination: {
            el: ".rts-testimonials-dot",
            clickable: true,
          },
          breakpoints: {
            1200: {
              slidesPerView: 3,
            },
            767: {
              slidesPerView: 2,
            },
            0: {
              slidesPerView: 1,
            }
          },
        })
      })
      $(document).ready(function () {
        let swiperthumb2;
        let swiper2;

        function initSwipers() {
          if (swiperthumb2) swiperthumb2.destroy(true, true);
          if (swiper2) swiper2.destroy(true, true);
          const isMobile = window.innerWidth <= 991;
          swiperthumb2 = new Swiper(".SlideThumb2", {
            direction: isMobile ? "horizontal" : "vertical",
            spaceBetween: 0,
            slidesPerView: isMobile ? 4 : 6,
            mousewheel: !isMobile,
            loop: true,
            watchSlidesProgress: true,
            navigation: {
              nextEl: ".thumb-next",
              prevEl: ".thumb-prev",
            },
            on: {
              resize: function () {
                this.update();
              },
            },
          });
          swiper2 = new Swiper(".rts-heroSlider2", {
            slidesPerView: 1,
            effect: "fade",
            spaceBetween: 0,
            speed: 1500,
            grabCursor: true,
            autoplay: false,
            loop: true,
            thumbs: {
              swiper: swiperthumb2,
            },
          });
        }

        initSwipers();

        $(window).on("resize", function () {
          initSwipers();
        });
      });
      $(document).ready(function() {
        var swiperthumb = new Swiper(".SlideThumb", {
          spaceBetween: 0,
          slidesPerView: 6,
          mousewheel: true,
          loop: true,
          watchSlidesProgress: true,
          navigation: {
            nextEl: ".thumb-next",
            prevEl: ".thumb-prev",
          },
          breakpoints: {
            1500: {
              slidesPerView: 6,
            },
            1199: {
              slidesPerView: 6,
            },
            991: {
              slidesPerView: 6,
            },
            767: {
              slidesPerView: 4,
            },
            575: {
              slidesPerView: 4,
            },
            0: {
              slidesPerView: 4,
            }
          },
        });
        var swipermain = new Swiper(".rts-heroSlider", {
          slidesPerView: 1,
          spaceBetween: 0,
          effect: "fade",
          speed: 1500,
          grabCursor: true,
          autoplay: false,
          loop: true,
          thumbs: {
            swiper: swiperthumb,
          },
        });
      });
    },
    stickyHeader: function () {
  let lastScrollTop = 0;

  $(window).on('scroll', function () {
    let scrollTop = $(this).scrollTop();

    if (scrollTop > 150 && lastScrollTop <= 150) {
      $('.header--sticky').addClass('sticky');
    } 
    
    if (scrollTop <= 150 && lastScrollTop > 150) {
      $('.header--sticky').removeClass('sticky');
    }

    lastScrollTop = scrollTop;
  });
},
    backToTopInit: function () {
      $(document).ready(function () {
        "use strict";

        var progressPath = document.querySelector('.progress-wrap path');
        var pathLength = progressPath.getTotalLength();
        progressPath.style.transition = progressPath.style.WebkitTransition = 'none';
        progressPath.style.strokeDasharray = pathLength + ' ' + pathLength;
        progressPath.style.strokeDashoffset = pathLength;
        progressPath.getBoundingClientRect();
        progressPath.style.transition = progressPath.style.WebkitTransition = 'stroke-dashoffset 10ms linear';
        var updateProgress = function () {
          var scroll = $(window).scrollTop();
          var height = $(document).height() - $(window).height();
          var progress = pathLength - (scroll * pathLength / height);
          progressPath.style.strokeDashoffset = progress;
        }
        updateProgress();
        $(window).scroll(updateProgress);
        var offset = 50;
        var duration = 550;
        jQuery(window).on('scroll', function () {
          if (jQuery(this).scrollTop() > offset) {
            jQuery('.progress-wrap').addClass('active-progress');
          } else {
            jQuery('.progress-wrap').removeClass('active-progress');
          }
        });
        jQuery('.progress-wrap').on('click', function (event) {
          event.preventDefault();
          jQuery('html, body').animate({ scrollTop: 0 }, duration);
          return false;
        })


      });
    },
    sideMenu: function () {
      // collups menu side right
      $(document).on('click', '#menu-btn', function () {
        $("#side-bar").addClass("show");
        $("#anywhere-home").addClass("show");
      });
      $(document).on('click', '.close-icon-menu', function () {
        $("#side-bar").removeClass("show");
        $("#anywhere-home").removeClass("show");
      });
      $(document).on('click', '#anywhere-home', function () {
        $("#side-bar").removeClass("show");
        $("#anywhere-home").removeClass("show");
      });
      $(document).on('click', '.onepage .mainmenu li a', function () {
        $("#side-bar").removeClass("show");
        $("#anywhere-home").removeClass("show");
      });
    },
    niceSelect: function () {
      (function ($) {
        $.fn.niceSelect = function (method) {
          if (typeof method === 'string') {
            return this.each(function () {
              var $select = $(this), $dropdown = $select.next('.nice-select');
              if (method === 'update') {
                if ($dropdown.length) $dropdown.remove();
                createNiceSelect($select);
              } else if (method === 'destroy') {
                if ($dropdown.length) $dropdown.remove();
                $select.show();
              }
            });
          }

          this.hide().each(function () {
            if (!$(this).next().hasClass('nice-select')) createNiceSelect($(this));
          });

          function createNiceSelect($select) {
            var $dropdown = $('<div class="nice-select" tabindex="0">' +
              '<span class="current"></span><ul class="list"></ul></div>');
            $select.after($dropdown);

            $select.find('option').each(function () {
              var $option = $(this);
              $('<li class="option" data-value="' + $option.val() + '">' +
                $option.text() + '</li>')
                .toggleClass('selected', $option.is(':selected'))
                .toggleClass('disabled', $option.is(':disabled'))
                .appendTo($dropdown.find('.list'));
            });

            $dropdown.find('.current').text($select.find('option:selected').text());
          }

          $(document).off('.nice_select')
            .on('click.nice_select', '.nice-select', function () {
              $('.nice-select').not(this).removeClass('open');
              $(this).toggleClass('open');
            })
            .on('click.nice_select', function (e) {
              if (!$(e.target).closest('.nice-select').length) $('.nice-select').removeClass('open');
            })
            .on('click.nice_select', '.nice-select .option:not(.disabled)', function () {
              var $option = $(this), $dropdown = $option.closest('.nice-select');
              $dropdown.find('.selected').removeClass('selected');
              $option.addClass('selected');
              $dropdown.find('.current').text($option.text());
              $dropdown.prev('select').val($option.data('value')).trigger('change');
            });

          return this;
        };

        $(document).ready(function () {
          $('select').niceSelect();
        });
      }(jQuery));
    },
    vedioActivation: function () {
      $(document).ready(function () {
        $('.popup-youtube, .popup-video').magnificPopup({
          type: 'iframe',
          mainClass: 'mfp-fade',
          removalDelay: 160,
          preloader: false,
          fixedContentPos: false
        });
      });
    },
    menuCurrentLink: function () {
      var currentPage = location.pathname.split("/"),
        current = currentPage[currentPage.length - 1];
      $('.parent-nav li > a').each(function () {
        var $this = $(this);
        if ($this.attr('href') === current) {
          $this.addClass('active');
          $this.parents('.has-dropdown').addClass('menu-item-open')
        }
      });
    },
    preloader: function () {
      window.addEventListener('load', function () {
        document.querySelector('body').classList.add("loaded")
      });
    },
    counterUp: function (e) {
      $('.counter').counterUp({
        delay: 10,
        time: 1000
      });
    },
    searchOption: function () {
      // search popup
      $(document).on('click', '.search', function () {
        $(".search-input-area").addClass("show");
        $("#anywhere-home").addClass("show");
      });
      $(document).on('click', '#close', function () {
        $(".search-input-area").removeClass("show");
        $("#anywhere-home").removeClass("show");
      });
      $(document).on('click', '#anywhere-home', function () {
        $(".search-input-area").removeClass("show");
        $("#anywhere-home").removeClass("show");
      });
    },
    mesonaryTab: function () {
      $(window).on("load", function () {
        var isotope = $(".main-isotop");

        if (isotope.length) {
          var iso = new Isotope(".filter", {
            itemSelector: ".element-item",
            layoutMode: "fitRows",
            fitRows: {
              equalheight: true,
            },
          });

          // filter functions
          var filterFns = {
            // show if name ends with -ium
            ium: function (itemElem) {
              var name = itemElem.querySelector(".name").textContent;
              return name.match(/ium$/);
            },
          };

          // bind filter button click
          var filtersElem = document.querySelector(".filters-button-group");
          filtersElem.addEventListener("click", function (event) {
            // only work with buttons
            if (!matchesSelector(event.target, "button")) {
              return;
            }
            var filterValue = event.target.getAttribute("data-filter");
            // use matching filter function
            filterValue = filterFns[filterValue] || filterValue;
            iso.arrange({ filter: filterValue });
          });

          // change is-checked class on buttons
          var buttonGroups = document.querySelectorAll(".rts-button-group");
          for (var i = 0, len = buttonGroups.length; i < len; i++) {
            var buttonGroup = buttonGroups[i];
            radioButtonGroup(buttonGroup);
          }
          function radioButtonGroup(buttonGroup) {
            buttonGroup.addEventListener("click", function (event) {
              // only work with buttons
              if (!matchesSelector(event.target, "button")) {
                return;
              }
              buttonGroup
                .querySelector(".is-checked")
                .classList.remove("is-checked");
              event.target.classList.add("is-checked");
            });
          }
        }

        if ($(".grid-masonary").length) {
          // image loaded portfolio init
          $(".grid-masonary").imagesLoaded(function () {
            $(".portfolio-filter").on("click", "button", function () {
              var filterValue = $(this).attr("data-filter");
              $grid.isotope({
                filter: filterValue,
              });
            });
            var $grid = $(".grid-masonary").isotope({
              itemSelector: ".grid-item-p",
              percentPosition: true,
              masonry: {
                columnWidth: ".grid-item-p",
              },
            });
          });
        }

        // portfolio Filter
        $(".portfolio-filter button").on("click", function (event) {
          $(this).siblings(".is-checked").removeClass("is-checked");
          $(this).addClass("is-checked");
          event.preventDefault();
        });
      });
    },
    magnificPopupActive: function () {
      /* magnificPopup img view */
      $('.gallery-image').magnificPopup({
        type: 'image',
        gallery: {
          enabled: true
        }
      });
    },
    datePicker: function () {
      $(document).ready(function () {
        $(".datepicker09").datepicker();
      });
    },
    containerResize: function () {
      $(document).ready(function () {
        gsap.registerPlugin(ScrollTrigger);
        var $videoArea = $(".rts-video-wrapper");
        gsap.set($videoArea, { scale: 0.9 });
        gsap.to($videoArea, {
          scale: 1,
          ease: "power1.out",
          scrollTrigger: {
            trigger: $videoArea,
            start: "top bottom",
            end: "top top",
            scrub: true,
            markers: false,
          },
        });
      });
    },
  }
  document.addEventListener("DOMContentLoaded", function () {
    var accordionHeaders = document.querySelectorAll(".accordion-header");
    accordionHeaders.forEach(function (header, index) {
      header.addEventListener("click", function () {
        var accordionItems = document.querySelectorAll(".accordion-item");
        accordionItems.forEach(function (item) {
          item.classList.remove("active");
        });
        var clickedItem = document.querySelectorAll(".accordion-item")[index];
        clickedItem.classList.add("active");
      });
    });
  });

  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();

      const targetId = this.getAttribute('href');

      if (targetId === '#') {
        // If the link is the document header, scroll to the top of the page
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      } else {
        // Get the target element
        const targetElement = document.querySelector(targetId);

        if (targetElement) {
          // Calculate the position 50px above the target element
          const targetPosition = targetElement.getBoundingClientRect().top + window.scrollY - 80;

          // Scroll to the calculated position
          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
          });
        }
      }
    });
  });

  // background image
  document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("[data-bg-src]").forEach(function (el) {
      const bg = el.getAttribute("data-bg-src");
      if (bg) {
        el.style.backgroundImage = `url(${bg})`;
        el.style.backgroundSize = "cover"; // Optional
        el.style.backgroundPosition = "center"; // Optional
        el.style.backgroundRepeat = "no-repeat"; // Optional
      }
    });
  });
  rtsJs.m();

})(jQuery, window)



