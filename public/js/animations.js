// Smooth reveal animation for cards
const observerOptions = {
  threshold: 0.1,
  rootMargin: "0px 0px -50px 0px",
}

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add("reveal")
      observer.unobserve(entry.target)
    }
  })
}, observerOptions)

// Observe all cards and service items
document.addEventListener("DOMContentLoaded", () => {
  const elements = document.querySelectorAll(".cliente-card, .servicio-item")
  elements.forEach((el) => observer.observe(el))

  initParticles()

  // Theme toggle functionality
  const themeToggle = document.getElementById("themeToggle")
  const body = document.body
  const sunIcon = themeToggle.querySelector(".fa-sun")
  const moonIcon = themeToggle.querySelector(".fa-moon")

  function setTheme(theme) {
    if (theme === "white") {
      body.classList.add("white-theme")
      sunIcon.style.display = "none"
      moonIcon.style.display = "inline-block"
    } else {
      body.classList.remove("white-theme")
      sunIcon.style.display = "inline-block"
      moonIcon.style.display = "none"
    }
    localStorage.setItem("theme", theme)
  }

  themeToggle.addEventListener("click", () => {
    const newTheme = body.classList.contains("white-theme") ? "dark" : "white"
    setTheme(newTheme)
  })

  // Check for saved theme preference
  const savedTheme = localStorage.getItem("theme") || "dark"
  setTheme(savedTheme)
})

// Particle background effect
function initParticles() {
  const canvas = document.createElement("canvas")
  canvas.id = "particle-background"
  document.body.prepend(canvas)

  const ctx = canvas.getContext("2d")
  const particles = []

  function resize() {
    canvas.width = window.innerWidth
    canvas.height = window.innerHeight
  }

  class Particle {
    constructor() {
      this.x = Math.random() * canvas.width
      this.y = Math.random() * canvas.height
      this.size = Math.random() * 2
      this.speedX = Math.random() * 0.5 - 0.25
      this.speedY = Math.random() * 0.5 - 0.25
      this.opacity = Math.random() * 0.5
    }

    update() {
      this.x += this.speedX
      this.y += this.speedY

      if (this.x > canvas.width) this.x = 0
      if (this.x < 0) this.x = canvas.width
      if (this.y > canvas.height) this.y = 0
      if (this.y < 0) this.y = canvas.height
    }

    draw() {
      ctx.fillStyle = `rgba(255, 59, 59, ${this.opacity})`
      ctx.beginPath()
      ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2)
      ctx.fill()
    }
  }

  function createParticles() {
    for (let i = 0; i < 50; i++) {
      particles.push(new Particle())
    }
  }

  function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height)
    particles.forEach((particle) => {
      particle.update()
      particle.draw()
    })
    requestAnimationFrame(animate)
  }

  window.addEventListener("resize", resize)
  resize()
  createParticles()
  animate()
}

