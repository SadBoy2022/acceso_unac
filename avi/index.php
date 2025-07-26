<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login UNAC</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      background-color: #276caa;
      height: 100vh;
      display: flex;
      align-items: center;
    }
    .login-card {
      border-radius: 1rem;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
    .login-image {
      border-radius: 1rem 0 0 1rem;
      height: 100%;
      object-fit: cover;
    }
    .btn-unac {
      background-color: #0056b3;
      color: white;
    }
    .btn-unac:hover {
      background-color: #003d7a;
    }
  </style>
</head>
<body>
  <div class="container py-5">
    <div class="row d-flex justify-content-center align-items-center">
      <div class="col col-xl-10">
        <div class="card login-card">
          <div class="row g-0">
            <div class="col-md-6 col-lg-5 d-none d-md-block">
              <img src="https://sga.unac.edu.pe/web/skins/ltr/com.bintenex.userinterface.skin.btx/Btx/Login/Login-bg-btx.jpg" 
                   alt="Login image" class="login-image w-100 h-100">
            </div>
            <div class="col-md-6 col-lg-7 d-flex align-items-center">
              <div class="card-body p-4 p-lg-5 text-dark">
                <form id="loginForm" method="post" onsubmit="event.preventDefault(); login()">
                  <div class="d-flex align-items-center mb-3 pb-1">
                    <img src="images/unac_logo.png" alt="UNAC Logo" width="180">
                  </div>
                  <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Ingresa con tu cuenta del SGA</h5>

                  <div class="form-floating mb-4">
                    <input type="text" id="username" name="username" class="form-control" required>
                    <label for="username"><i class="fas fa-user me-2"></i>Código de Alumno/Docente</label>
                  </div>

                  <div class="form-floating mb-4">
                    <input type="password" id="password" name="password" class="form-control" 
                           onkeydown="if(event.key === 'Enter') login()" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Contraseña</label>
                  </div>

                  <div class="pt-1 mb-4">
                    <button class="btn btn-unac btn-lg btn-block w-100" type="button" onclick="login()">
                      <i class="fas fa-sign-in-alt me-2"></i>Ingresar
                    </button>
                  </div>
                  
                  <div id="resultado" class="text-center fw-bold mt-3"></div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
  async function login() {
    const form = document.getElementById('loginForm');
    const formData = new FormData(form);
    const resultado = document.getElementById('resultado');
    resultado.textContent = "Verificando...";
    
    try {
      const response = await fetch('sga_api.php', {
        method: 'POST',
        body: formData
      });
      
      const data = await response.json();
      
      if (data.error) {
        resultado.textContent = data.error;
      } else if (data.data?.result === true) {
        Swal.fire({
          title: `Bienvenido ${formData.get('username')}`,
          html: `<p>Antes de continuar, por favor lee:</p>
                <ul class="text-start">
                  <li>Límite de 5 preguntas por hora</li>
                  <li>Solo para consultas sobre recuperación de acceso</li>
                  <li>Las consultas quedan registradas</li>
                </ul>`,
          icon: "success",
          confirmButtonText: "Aceptar",
          confirmButtonColor: "#0056b3"
        }).then(() => {
          window.location.href = 'chat.php';
        });
      } else {
        resultado.textContent = 'Usuario o contraseña incorrectos';
      }
    } catch (err) {
      resultado.textContent = 'Error de conexión';
      console.error(err);
    }
  }
  </script>
</body>
</html>
