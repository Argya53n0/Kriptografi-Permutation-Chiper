FROM php:8.2-apache

# Aktifkan mod_rewrite Apache jika dibutuhkan untuk routing
RUN a2enmod rewrite

# Beri akses tulis ke folder uploads (akan dibuat di dalam src nantinya)
RUN chown -R www-data:www-data /var/www/html/