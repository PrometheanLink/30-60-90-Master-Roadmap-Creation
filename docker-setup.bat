@echo off
REM Setup script for Windows - Sojourn Coaching Development Environment

echo ========================================
echo   Sojourn Coaching - Docker Setup
echo   30/60/90 Project Journey Plugin
echo   Kim Benedict - Sojourn Coaching
echo ========================================
echo.

echo Step 1: Building Docker containers...
echo   - sojourn-mysql (MySQL 8.0)
echo   - sojourn-wordpress (WordPress + PHP + Composer)
echo   - sojourn-phpmyadmin (Database Management)
docker-compose build

echo.
echo Step 2: Starting containers...
docker-compose up -d

echo.
echo Step 3: Waiting for WordPress to initialize (30 seconds)...
timeout /t 30 /nobreak

echo.
echo ========================================
echo   Sojourn Coaching Environment Ready!
echo ========================================
echo.
echo WordPress:     http://localhost:8675
echo phpMyAdmin:    http://localhost:8676
echo.
echo Container Names:
echo   - sojourn-wordpress
echo   - sojourn-mysql
echo   - sojourn-phpmyadmin
echo.
echo Next Steps:
echo 1. Go to http://localhost:8675 and complete WordPress installation
echo 2. Install "All-in-One WP Migration" plugin
echo 3. Import your .wpress file from walterh50.sg-host.com
echo 4. Run: docker-compose exec wordpress bash
echo 5. Then: cd wp-content/plugins/30-60-90-project-journey
echo 6. Then: composer install
echo.
echo Useful Commands:
echo   View logs:    docker-compose logs -f
echo   Stop:         docker-compose down
echo   Restart:      docker-compose restart
echo   Access shell: docker-compose exec wordpress bash
echo ========================================
pause
