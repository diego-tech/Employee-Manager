<h1>App de Cursos</h1>

API realizada con Laravel para una aplicación de cursos con los siguientes requisitos:

1. Registro de empleados: solo accesible para usuarios directivos o de RRHH.
Debe permitir insertar los siguientes datos:
Nombre
Email (único).
Contraseña segura (al menos 6 caracteres, con al menos una letra en mayúsculas y al menos un número).
Puesto de trabajo: a elegir entre Dirección, RRHH y Empleado.
Salario.
Biografía.
2. Login mediante email y contraseña.
3. Recuperar contraseña, introduciendo el email. Genera una nueva contraseña aleatoria y envía un email.
4. Lista de empleados: solo accesible para directivos y RRHH. Muestra Nombres, puestos de trabajo y salarios de los empleados normales. Si es directivo, también muestra los datos de los empleados de RRHH.
5. Detalle del empleado: solo accesible para directivos y RRHH. Muestra nombre, email, puesto de trabajo, biografía y salario de un empleado normal. Si el usuario es directivo, también puede consultar esta información de los usuarios de RRHH.
6. Ver perfil: accesible a cualquier usuario logeado. Muestra los datos completos del usuario que ha iniciado sesión.
7.  Modificar datos de empleado: solo accesible para directivos y RRHH. Permite modificar los datos de un empleado normal indicando los nuevos datos. Si es un usuario directivo, también puede modificar los datos de los usuarios de RRHH y los suyos propios, pero no los de otro directivo.

Develop by Diego Muñoz Herranz | <a href="https://www.dmunoz.dev/" target="_blank">dmunoz.dev</a> | <a href="https://www.linkedin.com/in/diego-mu%C3%B1oz-herranz-b03a42182/" target="_blank"> Linkedin</a>
