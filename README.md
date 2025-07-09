# **Replicación de Base de Datos Cineplanet**

Este proyecto busca replicar la funcionalidad principal de la base de datos de Cineplanet, permitiendo la gestión de películas, dulcería, usuarios y transacciones. La aplicación está desarrollada utilizando **PHP** para la lógica del servidor, **SQL** para la gestión de la base de datos, y **HTML**, **CSS** y **JavaScript** para la interfaz de usuario.

## **Características**

- **Inicio de Sesión:** Los usuarios pueden iniciar sesión como **usuario normal** o como **administrador**.
- **Gestión de Dulcería (Solo Administrador):**
  - Agregar nuevos productos de dulcería.
  - Eliminar productos de dulcería existentes.
- **Gestión de Películas (Solo Administrador):**
  - Agregar nuevas películas.
  - Eliminar películas existentes.
- **Realización de Compras (Cliente):**
  - Los usuarios pueden simular la compra de entradas de cine y productos de dulcería.
  - Se aplica un **descuento especial** para los clientes que son socios.

## **Tecnologías Utilizadas**

- **Backend:** PHP
- **Base de Datos:** SQL (compatible con MySQL, PostgreSQL, etc.)
- **Frontend:**
  - HTML
  - CSS
  - JavaScript

## **Instalación**

1. **Clona el repositorio:**  
   git clone \<https://github.com/FVargas2077/Cineplanet-Database>

2. **Configura la base de datos:**
   - Crea una base de datos SQL "Cine_DB".
   - Importa el esquema de la base de datos y los datos de ejemplo (si los hay) desde el archivo database.sql (o el nombre correspondiente).
   - Asegúrate de que tus credenciales de base de datos en el archivo de conexión PHP (config.php o similar) sean correctas.
3. **Configura el servidor web:**
   - Coloca los archivos del proyecto en la carpeta raíz de tu servidor web (por ejemplo, htdocs para Apache o www para Nginx).
   - Asegúrate de tener un servidor PHP configurado y funcionando (XAMPP, WAMP, MAMP son opciones populares para desarrollo local).

## **Uso**

1. Abre tu navegador web y navega a la URL donde has desplegado el proyecto (por ejemplo, http://localhost/cineplanet/index.php).
2. Podrás iniciar sesión con las credenciales predeterminadas (si las hay) o registrarte como un nuevo usuario.
3. Explora las funcionalidades como usuario normal o como administrador para probar las características de gestión y compra.

## **Contribuciones**

Las contribuciones son bienvenidas. Si deseas mejorar este proyecto, por favor, sigue estos pasos:

1. Haz un "fork" de este repositorio.
2. Crea una nueva rama (git checkout \-b feature/nueva-funcionalidad).
3. Realiza tus cambios y haz "commit" de ellos (git commit \-am 'Añade nueva funcionalidad X').
4. Sube tus cambios a tu "fork" (git push origin feature/nueva-funcionalidad).
5. Crea un "Pull Request" describiendo tus cambios.
