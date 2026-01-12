# ✈️ Prueba Técnica: Sistema de Gestión de Proveedores (VPT)

Proyecto desarrollado para el departamento de contabilidad de **Viajes Para Ti**, enfocado en la agilidad, la integridad de los datos y una experiencia de usuario (UX) de nivel premium.

---

## 🚀 Decisiones Técnicas y Arquitectura

Para este proyecto se ha priorizado un código **mantenible, escalable y robusto**, aplicando las siguientes estrategias:

### 1. Interfaz SPA-like (Single Page Application)
- **Flujo mediante Modales**: Todo el CRUD se gestiona desde una única vista. El formulario se carga dinámicamente vía **Fetch API (AJAX)**, evitando recargas innecesarias y mejorando la velocidad operativa.
- **Micro-interacciones**: Se han implementado estados de carga en botones, transiciones fluidas con CSS (Inter Font, Soft Shadows) y auto-cierre de notificaciones tras 4 segundos.

### 2. Gestión Avanzada de Datos (DataTables)
- **Filtrado Acumulativo**: Sistema de etiquetas (badges) dinámicos que permiten filtrar por múltiples tipos de proveedor simultáneamente mediante expresiones regulares.
- **Localización Dinámica**: El motor de búsqueda y la interfaz de la tabla se adaptan automáticamente al idioma seleccionado.

### 3. Calidad y Seguridad (Backend)
- **Borrado Lógico y Gestión de Unicidad**: Se ha implementado un sistema de **Soft Delete** mediante el campo `active` para cumplir con los requisitos de integridad del departamento de contabilidad. 
Para resolver el conflicto técnico con los índices `UNIQUE` de la base de datos (que impedirían registrar un nuevo proveedor con los mismos datos de uno previamente borrado), el sistema aplica una **anonimización automática** del registro desactivado en el momento de la ejecución. Al "eliminar", se añade un sufijo de sistema único a los campos críticos (Nombre, Email y Teléfono), liberando los valores originales de forma inmediata para nuevos registros, pero preservando el histórico completo para futuras auditorías o consultas contables.
- **Validación de Dominio**: Uso de `UniqueEntity` para garantizar que no existan nombres, emails o teléfonos duplicados, y validaciones de formato mediante **Regex**.
- **Exportación Orientada a Negocio**: Generador de reportes CSV con codificación UTF-8 BOM para una compatibilidad total con Microsoft Excel.

### 4. UI/UX Multitarea
- **Modo Oscuro Nativo**: Soporte completo para temas Light/Dark con persistencia en `LocalStorage`.
- **Internacionalización (i18n)**: Soporte completo para **5 idiomas**: Castellano, Inglés, Francés, Alemán y Catalán.

---

## 🛠️ Instalación y Despliegue (Docker)

El proyecto incluye un `Makefile` para automatizar la configuración inicial en un entorno Dockerizado.

### Requisitos previos
- Docker y Docker Compose.
- `make` (opcional, pero recomendado).

### Inicio rápido (Recomendado)
Desde la raíz del proyecto, ejecuta:
```bash
make setup
```
Este comando levantará los contenedores, instalará dependencias, ejecutará migraciones y cargará datos de prueba automáticamente.
Comandos manuales (Alternativa)
Si no dispones de make, ejecuta los siguientes comandos:

# 1. Levantar contenedores
```bash
docker-compose up -d
```

# 2. Instalar dependencias de PHP
```bash
docker-compose exec php composer install
```

# 3. Preparar base de datos
```bash
docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction
```

# 4. Cargar datos de prueba (Fixtures)
```bash
docker-compose exec php bin/console doctrine:fixtures:load --no-interaction
```
La aplicación estará disponible en: http://localhost:8000


# 📂 Estructura del Proyecto
- src/Controller/: Controladores documentados bajo estándar PHPDoc con inyección de dependencias por constructor.
- src/Entity/Traits/: Uso de TimestampableTrait para gestión automática de fechas (escalabilidad).
- translations/: Diccionarios YAML para los 5 idiomas soportados.
- public/: Contiene el favicon y assets estáticos.
- Candidato: Guillem Masdeu de María
- Tecnologías: Symfony 7, PHP 8.2, Docker, MySQL, Bootstrap 5.3, DataTables.

### Notas Finales para tu entrega:
1.  **Makefile:** Asegúrate de que tu archivo `Makefile` tiene los comandos que menciono (`setup`).
2.  **Fixtures:** Si no instalaste el componente de fixtures, omite esa parte del README o instálalo rápido (`composer require --dev orm-fixtures`).
3.  **URL:** He puesto el puerto `8000`, asegúrate de que es el que definiste en tu `docker-compose.yml`.
