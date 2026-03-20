# Guia de Deployment - Klinike Dating App

## Opciones de Hosting PHP+MySQL

### Gratis ($0/mes)

| Proveedor | PHP | MySQL | Almacenamiento | Ancho de Banda | Limitaciones |
|-----------|-----|-------|----------------|----------------|-------------|
| **InfinityFree** | 4.4 - 8.0 | Si (400 DBs) | 5 GB | ~50K hits/dia | Sin SSH, sin email, 10 MB max archivo |
| **GoogieHost** | 5.3+ | Si (2 DBs) | 1 GB SSD | Ilimitado | Max 2 dominios |
| **AwardSpace** | Actual | Si (1 DB) | 250 MB | 5 GB/mes | Sin cron jobs, sin backups |
| **ByetHost** | Si | Si | 1 GB | 50 GB/mes | VistaPanel (no cPanel) |
| **FreeHosting.com** | Si | Si (1 DB) | 10 GB | Ilimitado | Sin SSL gratis, sin subdominio gratis |

### Pagados Baratos (< $3/mes)

| Proveedor | Precio | PHP | MySQL | Almacenamiento | Notas |
|-----------|--------|-----|-------|----------------|-------|
| **IONOS** | $1.00/mes (1er ano) | 8.x | Ilimitado | Ilimitado | Dominio gratis 1er ano |
| **Namecheap (Stellar)** | $1.58/mes | 5.x - 8.x | 50 DBs | 20 GB SSD | 3 sitios, cPanel |
| **Hostinger (Premium)** | $2.49/mes | 5.2 - 8.2 | Ilimitado | 100 GB SSD | 100 sitios, acepta crypto |
| **GreenGeeks** | $2.95/mes | 5.6 - 8.2 | Ilimitado | 50 GB SSD | 1 sitio, eco-friendly |
| **SiteGround** | $2.99/mes (1er periodo) | 7.3 - 8.4 | Si | 10 GB SSD | Renueva a $17.99/mes |

> **Nota:** La mayoria requiere pago de 12-36 meses por adelantado para obtener el precio anunciado.

---

## Hosting con Privacidad (Acepta Crypto)

| Proveedor | Jurisdiccion | Crypto | KYC | Precio |
|-----------|-------------|--------|-----|--------|
| **FlokiNET** | Islandia/Rumania/Finlandia | BTC, LTC, DASH+ | No (solo email) | ~EUR 3.50/mes |
| **Hostinger** | Lituania | BTC + crypto | No verificacion ID | $2.49/mes |
| **Namecheap** | USA | Crypto | No verificacion ID | $1.58/mes |
| **Alexhost** | Moldavia | BTC, ETH, LTC+ | Minimo | ~$1.99/mes |

---

## Opciones de Dominio

### Subdominios Gratis

| Servicio | Formato | Notas |
|----------|---------|-------|
| **EU.org** | tunombre.eu.org | Aprobacion ~14 dias |
| **is-a.dev** | tunombre.is-a.dev | Registro via GitHub PR |
| **FreeDNS (afraid.org)** | Varios dominios | DNS dinamico + estatico |

### Dominios Ultra-Baratos

| TLD | Precio | Donde Comprar |
|-----|--------|--------------|
| .xyz | ~$1/ano | Namecheap, Cloudflare |
| .online | ~$0.99/ano | Hostinger |
| .shop | ~$0.99/ano | Hostinger |
| .site | ~$1-3/ano | Namecheap |
| Gratis con hosting | $0 | Hostinger, Namecheap, IONOS |

---

## Certificados SSL Gratis

| Proveedor | Duracion | Wildcard | Notas |
|-----------|----------|----------|-------|
| **Let's Encrypt** | 90 dias (auto-renew) | Si | Estandar de la industria, CLI (certbot) |
| **Cloudflare SSL** | Perpetuo | Si | Incluye CDN + DDoS + WAF gratis |
| **ZeroSSL** | 90 dias | Si | Dashboard web + ACME |

---

## Combinaciones Recomendadas

### Costo Cero ($0/mes)
- **Hosting:** InfinityFree o GoogieHost
- **Dominio:** EU.org subdomain o is-a.dev
- **SSL:** Cloudflare free plan

### Maxima Privacidad, Costo Minimo (~$1.50-2.50/mes)
- **Hosting:** Hostinger o Namecheap (pago con crypto)
- **Dominio:** .xyz desde Namecheap ($1/ano, pago crypto)
- **SSL:** Let's Encrypt (auto-renew)

### Maxima Privacidad, Offshore (~$5-7/mes)
- **Hosting:** FlokiNET Romania (BTC, sin KYC)
- **Dominio:** Namecheap con WHOIS privacy (pago crypto)
- **SSL:** Let's Encrypt

---

## Instrucciones de Deploy

### 1. Preparar Base de Datos

```sql
-- Importar el schema SQL ubicado en:
-- dating-app/sql/

-- Crear la base de datos y usuario en tu hosting:
CREATE DATABASE klinike_dating;
CREATE USER 'klinike_user'@'localhost' IDENTIFIED BY 'tu_password_seguro';
GRANT ALL PRIVILEGES ON klinike_dating.* TO 'klinike_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Configurar la Aplicacion

Editar la configuracion de base de datos en `config/config/`:

```php
// Actualizar credenciales de DB
$db_host = 'localhost';
$db_name = 'klinike_dating';
$db_user = 'klinike_user';
$db_pass = 'tu_password_seguro';
```

### 3. Subir Archivos

```bash
# Subir todo el contenido de dating-app/ al directorio public_html/
# Subir config/ fuera de public_html por seguridad

# Estructura recomendada:
# /home/user/config/         <- configuracion (fuera de web root)
# /home/user/public_html/    <- contenido de dating-app/
```

### 4. Permisos de Archivos

```bash
# Directorios
find . -type d -exec chmod 755 {} \;

# Archivos PHP
find . -type f -name "*.php" -exec chmod 644 {} \;

# Directorio de uploads (escritura)
chmod 775 uploads/
chmod 775 uploads/photos/
chmod 775 uploads/profiles/
```

### 5. Configurar SSL

```bash
# Con Let's Encrypt (si tienes SSH):
sudo certbot --apache -d tudominio.com

# O usar Cloudflare:
# 1. Agregar sitio en cloudflare.com
# 2. Cambiar nameservers del dominio a Cloudflare
# 3. Activar SSL mode "Full (Strict)"
```

### 6. Verificar .htaccess

El archivo `dating-app/.htaccess` ya esta configurado. Verificar que `mod_rewrite` este habilitado en el servidor.

---

## Checklist Post-Deploy

- [ ] Base de datos importada y funcionando
- [ ] Configuracion de DB actualizada con credenciales correctas
- [ ] Archivos subidos al servidor
- [ ] Permisos de archivos configurados correctamente
- [ ] SSL/HTTPS funcionando
- [ ] Directorio uploads/ con permisos de escritura
- [ ] .htaccess funcionando (mod_rewrite habilitado)
- [ ] Probar registro de usuario
- [ ] Probar login
- [ ] Probar subida de fotos
