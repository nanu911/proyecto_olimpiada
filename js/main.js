$(document).ready(() => {
  // Inicializar AOS con configuración mejorgada
  AOS.init({
    duration: 600,
    easing: "ease-out",
    once: true,
    offset: 50,
    disable: "mobile", // Deshabilitar en móviles para mejor performance
  })

  // Navegación móvil
  $(".hamburger").click(function () {
    $(".nav-menu").toggleClass("active")
    $(this).toggleClass("active")
  })

  // Cerrar menú móvil al hacer click en un enlace
  $(".nav-link").click(() => {
    $(".nav-menu").removeClass("active")
    $(".hamburger").removeClass("active")
  })

  // Efecto de escritura para el título hero
  function typeWriter(element, text, speed = 80) {
    let i = 0
    element.text("")

    function type() {
      if (i < text.length) {
        element.text(element.text() + text.charAt(i))
        i++
        setTimeout(type, speed)
      } else {
        // Remover cursor después de completar
        setTimeout(() => {
          element.removeClass("typing-text")
        }, 2000)
      }
    }

    type()
  }

  // Iniciar efecto de escritura cuando la página carga
  const heroTitle = $(".hero-title")
  const titleText = heroTitle.data("text")
  if (titleText) {
    const typingElement = heroTitle.find(".typing-text")
    if (typingElement.length) {
      typingElement.addClass("typing-text")
      setTimeout(() => {
        typeWriter(typingElement, titleText, 60)
      }, 1000)
    }
  }

  // Sistema de notificaciones mejorado
  function showNotification(message, type = "info") {
    const notification = $(`
      <div class="notification notification-${type} animate__animated animate__fadeInRight">
        <span>${message}</span>
        <button class="notification-close">&times;</button>
      </div>
    `)

    $("body").append(notification)

    // Auto-cerrar después de 4 segundos
    setTimeout(() => {
      notification.removeClass("animate__fadeInRight").addClass("animate__fadeOutRight")
      setTimeout(() => {
        notification.remove()
      }, 300)
    }, 4000)

    // Cerrar manualmente
    notification.find(".notification-close").click(() => {
      notification.removeClass("animate__fadeInRight").addClass("animate__fadeOutRight")
      setTimeout(() => {
        notification.remove()
      }, 300)
    })
  }

  // Contador de carrito
  function updateCartCounter() {
    $.get(
      "ajax/cart-count.php",
      (data) => {
        if (data.count > 0) {
          $(".cart-link").html(`<i class="fas fa-shopping-cart"></i> Carrito (${data.count})`)
        } else {
          $(".cart-link").html(`<i class="fas fa-shopping-cart"></i> Carrito`)
        }
      },
      "json",
    ).fail(() => {
      // Si falla, no hacer nada
      console.log("Error al actualizar contador del carrito")
    })
  }

  // Agregar al carrito
  $(document).on("click", ".add-to-cart", function () {
    const productId = $(this).data("id")
    const button = $(this)
    const originalHtml = button.html()

    console.log("Agregando producto ID:", productId) // Debug

    button.prop("disabled", true).html('<span class="loading"></span> Agregando...')

    $.ajax({
      url: "ajax/add-to-cart.php",
      method: "POST",
      data: { producto_id: productId },
      dataType: "json",
      success: (response) => {
        console.log("Respuesta del servidor:", response) // Debug

        if (response.success) {
          button.removeClass("btn-secondary").addClass("btn-success").html('<i class="fas fa-check"></i> Agregado')

          // Mostrar notificación
          showNotification("Producto agregado al carrito", "success")

          // Actualizar contador del carrito
          updateCartCounter()

          // Restaurar botón después de 3 segundos
          setTimeout(() => {
            button.removeClass("btn-success").addClass("btn-secondary").html(originalHtml).prop("disabled", false)
          }, 3000)
        } else {
          showNotification(response.message || "Error al agregar producto", "error")
          button.html(originalHtml).prop("disabled", false)
        }
      },
      error: (xhr, status, error) => {
        console.log("Error AJAX:", xhr.responseText) // Debug
        showNotification("Error de conexión", "error")
        button.html(originalHtml).prop("disabled", false)
      },
    })
  })

  // Controles de cantidad en el carrito
  $(document).on("click", ".qty-plus", function () {
    const input = $(this).siblings(".quantity-input")
    const currentVal = Number.parseInt(input.val())
    const maxVal = Number.parseInt(input.attr("max"))
    if (currentVal < maxVal) {
      input.val(currentVal + 1)
    }
  })

  $(document).on("click", ".qty-minus", function () {
    const input = $(this).siblings(".quantity-input")
    const currentVal = Number.parseInt(input.val())
    const minVal = Number.parseInt(input.attr("min"))
    if (currentVal > minVal) {
      input.val(currentVal - 1)
    }
  })

  // Actualizar contador al cargar la página
  if ($(".cart-link").length) {
    updateCartCounter()
  }

  // Smooth scroll para enlaces internos
  $('a[href^="#"]').click(function (e) {
    e.preventDefault()
    const target = $($(this).attr("href"))
    if (target.length) {
      $("html, body").animate(
        {
          scrollTop: target.offset().top - 80,
        },
        600,
      )
    }
  })

  // Validación de formularios en tiempo real
  $(".form-control").on("blur", function () {
    const field = $(this)
    const value = field.val().trim()

    if (field.prop("required") && !value) {
      field.addClass("is-invalid")
    } else {
      field.removeClass("is-invalid")
    }

    // Validación específica para email
    if (field.attr("type") === "email" && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(value)) {
        field.addClass("is-invalid")
      } else {
        field.removeClass("is-invalid")
      }
    }
  })

  // Parallax effect suave para hero
  $(window).scroll(function () {
    const scrolled = $(this).scrollTop()
    const parallax = $(".hero-background")
    const speed = scrolled * 0.3

    if (parallax.length) {
      parallax.css("transform", `translateY(${speed}px)`)
    }
  })

  // Animaciones adicionales para cards
  $(".product-card, .feature-card, .testimonial-card").hover(
    function () {
      $(this).addClass("animate__animated animate__pulse")
    },
    function () {
      $(this).removeClass("animate__animated animate__pulse")
    },
  )

  // Contador animado para estadísticas
  function animateCounter(element, target, duration = 2000) {
    let start = 0
    const increment = target / (duration / 16)

    function updateCounter() {
      start += increment
      if (start < target) {
        element.text(Math.floor(start) + "+")
        requestAnimationFrame(updateCounter)
      } else {
        element.text(target + "+")
      }
    }

    updateCounter()
  }

  // Iniciar contadores cuando entran en viewport
  if (typeof IntersectionObserver !== "undefined") {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const statNumber = $(entry.target).find(".stat-number")
          const targetValue = Number.parseInt(statNumber.text().replace(/\D/g, ""))

          if (targetValue && !statNumber.hasClass("animated")) {
            statNumber.addClass("animated")
            if (targetValue === 50) {
              animateCounter(statNumber, 50)
            } else if (targetValue === 10) {
              statNumber.text("10K+")
            } else if (targetValue === 4) {
              statNumber.text("4.9")
            }
          }

          observer.unobserve(entry.target)
        }
      })
    })

    $(".stat-item").each(function () {
      observer.observe(this)
    })
  }

  // Hacer global la función showNotification
  window.showNotification = showNotification
})
