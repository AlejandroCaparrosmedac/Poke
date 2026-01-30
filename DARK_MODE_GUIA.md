# 🌙 DARK MODE - GUÍA DE USO

## ¿Qué es el Dark Mode?

El Dark Mode es un tema oscuro que reduce la fatiga visual en entornos con poca luz. La Pokédex ahora incluye un sistema completo de cambio entre modo claro y oscuro.

---

## 🎨 Características del Dark Mode

✅ **Tema oscuro completo**
- Interfaz oscura en todas las páginas
- Colores optimizados para leer en la oscuridad
- Gradientes adaptados al modo oscuro

✅ **Cambio fluido**
- Transiciones suaves al cambiar de tema
- Sin parpadeos ni cambios abruptos
- Animaciones elegantes

✅ **Persistencia**
- La preferencia se guarda en el navegador
- Se mantiene la selección entre sesiones
- No requiere login para cambiar tema

✅ **Detección automática**
- Detecta la preferencia del sistema operativo
- Se adapta automáticamente si no hay preferencia guardada
- Respeta las configuraciones de accesibilidad

---

## 🖱️ Cómo usar el Dark Mode

### Cambiar de tema
1. Busca el botón de tema en la esquina superior derecha (🌙 o ☀️)
2. Haz clic en el botón
3. El tema cambiará inmediatamente

### Ubicaciones del botón
- **Página de inicio**: Esquina superior derecha
- **Login**: Esquina superior derecha
- **Registro**: Esquina superior derecha
- **Pokédex**: En el navbar, junto a "Cerrar Sesión"
- **Favoritos**: En el navbar, junto a "Cerrar Sesión"

---

## 🎯 Iconos del botón

| Icono | Significado | Acción |
|-------|-------------|--------|
| 🌙 | Modo claro activo | Haz clic para activar modo oscuro |
| ☀️ | Modo oscuro activo | Haz clic para activar modo claro |

---

## 💾 Cómo funciona la persistencia

El Dark Mode guarda tu preferencia usando **localStorage** del navegador:

1. **Primera vez**: Se detecta la preferencia del sistema
2. **Cambio manual**: Se guarda tu elección
3. **Próximas visitas**: Se aplica tu preferencia guardada
4. **Cambio de dispositivo**: Cada navegador tiene su propia preferencia

---

## 🎨 Colores por tema

### Modo Claro
- Fondo: Blanco/Gris claro (#f8f9fa)
- Texto: Negro (#212529)
- Cards: Blanco (#ffffff)
- Navbar: Gradiente púrpura/azul

### Modo Oscuro
- Fondo: Gris oscuro (#1a1a1a, #2d2d2d)
- Texto: Blanco/Gris claro (#e0e0e0)
- Cards: Gris oscuro (#2d2d2d)
- Navbar: Gradiente púrpura/azul oscuro

---

## 🔧 Archivos técnicos

### CSS
`public/css/darkmode.css`
- Estilos para modo oscuro
- Variables CSS personalizadas
- Transiciones suaves

### JavaScript
`public/js/darkmode.js`
- Gestión del tema
- Persistencia en localStorage
- Detección del sistema operativo

---

## ⌨️ Compatibilidad

✅ Funciona en todos los navegadores modernos:
- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Opera 76+

---

## 🌐 Preferencia del sistema

Si no tienes una preferencia guardada, el Dark Mode se adapta automáticamente:

### Windows
- Configuración → Personalización → Colores
- Selecciona "Oscuro" o "Claro"

### macOS
- System Preferences → General
- Selecciona "Dark" o "Light"

### Linux
- Depende del entorno de escritorio (GNOME, KDE, etc.)

---

## 💡 Consejos

1. **Para leer en la oscuridad**: Activa Dark Mode
2. **Para mejor contraste**: Prueba ambos temas
3. **En dispositivos móviles**: El Dark Mode reduce el consumo de batería
4. **Accesibilidad**: Algunos usuarios lo encuentran más fácil de leer

---

## 🐛 Troubleshooting

### El tema no cambia
1. Recarga la página (Ctrl+R o Cmd+R)
2. Borra el caché del navegador
3. Verifica que JavaScript está activado

### El tema no se guarda
1. Verifica que localStorage está habilitado
2. Comprueba la configuración de privacidad del navegador
3. Intenta en una pestaña de navegación normal (no incógnito)

### Colores incorrectos
1. Asegúrate de que el CSS se cargó correctamente (F12 → Network)
2. Recarga la página completamente (Ctrl+Shift+R)
3. Limpia el caché (Ctrl+Shift+Delete)

---

## 🚀 Mejoras futuras

- [ ] Selector de tema en preferencias del usuario
- [ ] Temas adicionales (azul, verde, etc.)
- [ ] Sincronización entre dispositivos
- [ ] Horario automático (oscuro por la noche)
- [ ] Personalización de colores

---

**¡Disfruta del Dark Mode! 🌙✨**
