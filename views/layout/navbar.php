<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php">Sistema de Gestión</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownCarrera" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Carreras
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownCarrera">
                        <a class="dropdown-item" href="index.php?modulo=carrera">Ver Carreras</a>
                        <a class="dropdown-item" href="index.php?modulo=carrera&action=crear">Crear Carrera</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownJornada" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Jornadas
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownJornada">
                        <a class="dropdown-item" href="index.php?modulo=jornada">Ver Jornadas</a>
                        <a class="dropdown-item" href="index.php?modulo=jornada&action=crear">Crear Jornada</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownNivel" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Niveles
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownNivel">
                        <a class="dropdown-item" href="index.php?modulo=nivel">Ver Niveles</a>
                        <a class="dropdown-item" href="index.php?modulo=nivel&action=crear">Crear Nivel</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownParalelo" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Paralelo
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownParalelo">
                        <a class="dropdown-item" href="index.php?modulo=paralelo">Ver paralelo</a>
                        <a class="dropdown-item" href="index.php?modulo=paralelo&action=crear">Crear Paralelos</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAsignatura" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Asignaturas
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownAsignatura">
                        <a class="dropdown-item" href="index.php?modulo=asignatura">Ver Asignaturas</a>
                        <a class="dropdown-item" href="index.php?modulo=asignatura&action=crear">Crear Asignatura</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAsignar" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Asignar Materias
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownAsignatura">
                        <a class="dropdown-item" href="index.php?modulo=docente">Ver Asignacion</a>
                        <a class="dropdown-item" href="index.php?modulo=docente&action=crear">Crear Asignacion</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMatricula" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Matricula
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMatricula">
                        <a class="dropdown-item" href="index.php?modulo=matricula">Ver Matricula</a>
                        <a class="dropdown-item" href="index.php?modulo=matricula&action=crear">Crear Matricula</a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUsuario" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Usuario
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownUsuario">
                        <a class="dropdown-item" href="index.php?modulo=usuario">Ver usuarios</a>
                        <a class="dropdown-item" href="index.php?modulo=usuario&action=crear">Crear Usuarios</a>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="views/logout.php">Cerrar Sesión</a>
                </li>

            </ul>
        </div>
    </nav>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
