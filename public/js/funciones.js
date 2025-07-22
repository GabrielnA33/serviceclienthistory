document.addEventListener("DOMContentLoaded", () => {
  const inputBusqueda = document.getElementById("busqueda")
  const container = document.getElementById("clientes-lista")
  let pagina = 1
  let cargando = false
  const limiteClientes = 10

  function cargarClientes(filtro = "", nuevaPagina = 1, limpiar = false) {
    if (cargando) return
    cargando = true

    const url = `../controllers/clientesController.php?search=${encodeURIComponent(filtro)}&pagina=${nuevaPagina}&limite=${limiteClientes}`

    fetch(url)
      .then((response) => {
        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`)
        return response.json()
      })
      .then((clientes) => {
        if (clientes.error) {
          console.error("Error en la respuesta:", clientes.error)
          if (nuevaPagina === 1) container.innerHTML = `<p>${clientes.error}</p>`
          cargando = false
          return
        }

        if (clientes.length === 0) {
          if (nuevaPagina === 1) container.innerHTML = "<p>No se encontraron clientes</p>"
          cargando = false
          return
        }

        if (limpiar) {
          container.innerHTML = ""
          pagina = 1
        }

        clientes.forEach((cliente) => {
          const card = document.createElement("div")
          card.classList.add("cliente-card")

          card.innerHTML = `
                        <h3>${cliente.NOMBRE}</h3>
                        <p><strong>Localidad:</strong> ${cliente.LOCALIDAD}</p>
                        <p><strong>Dirección:</strong> ${cliente.CALLE || ""} ${cliente.NRO || ""} ${cliente.PISO ? `Piso ${cliente.PISO}` : ""} ${cliente.DTO ? `Dto ${cliente.DTO}` : ""}</p>
                        <p><strong>Teléfono:</strong> ${cliente.TEL1 || "Sin Teléfono"}</p>
                        <p><strong>OBSTEC:</strong> ${cliente.OBSTEC}</p>
                        <a href="cliente.php?codigo=${cliente.CODIGO}" class="button ver-mas" data-codigo="${cliente.CODIGO}">Ver Más</a>
                    `

          container.appendChild(card)
        })

        cargando = false
        pagina++
      })
      .catch((error) => {
        console.error("Error cargando los clientes:", error)
        if (nuevaPagina === 1) container.innerHTML = "<p>Error al cargar los clientes</p>"
        cargando = false
      })
  }

  inputBusqueda.addEventListener("input", () => {
    const filtro = inputBusqueda.value.trim()
    if (filtro.length > 0) {
      cargarClientes(filtro, 1, true)
    } else {
      cargarClientes("", 1, true)
    }
  })

  window.addEventListener("scroll", () => {
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 150) {
      cargarClientes(inputBusqueda.value.trim(), pagina)
    }
  })

  document.addEventListener("click", (event) => {
    const boton = event.target.closest(".ver-mas")
    if (boton) {
      event.preventDefault()
      const codigo = boton.getAttribute("data-codigo")
      if (codigo) {
        window.location.href = `cliente.php?codigo=${codigo}`
      }
    }
  })

  cargarClientes()
})

