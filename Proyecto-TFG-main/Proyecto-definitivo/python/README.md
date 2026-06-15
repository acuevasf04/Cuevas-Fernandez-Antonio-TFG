# Gestor de Usuarios — Windows Server

Interfaz gráfica en Python para crear y eliminar usuarios locales de Windows Server mediante scripts PowerShell.

## Requisitos

- Python 3.8+ con Tkinter
- Windows Server 2016/2019/2022
- PowerShell 5.1+
- Ejecutar como **Administrador**

## Estructura

```
gestor-usuarios/
├── gestor_usuarios.py
└── scripts/
    ├── crear_usuario.ps1
    ├── eliminar_usuario.ps1
    └── listar_usuarios.ps1
```

## Uso

1. Permitir ejecución de scripts (PowerShell como Admin):
   ```powershell
   Set-ExecutionPolicy RemoteSigned -Scope LocalMachine
   ```

2. Lanzar la aplicación:
   ```bash
   python gestor_usuarios.py
   ```

## Funcionalidades

- **Alta**: introduce usuario, contraseña y grupo opcional → pulsa *Crear usuario*
- **Baja**: introduce el nombre del usuario, marca la casilla de confirmación → pulsa *Eliminar usuario*
- **Log**: el panel derecho muestra en tiempo real el resultado de cada operación

## Scripts PowerShell

| Script | Parámetros |
|---|---|
| `crear_usuario.ps1` | `-Usuario` `-Password` `[-Grupo]` |
| `eliminar_usuario.ps1` | `-Usuario` |
| `listar_usuarios.ps1` | *(ninguno)* |

> Proyecto de prácticas para el módulo de Administración de Sistemas Operativos.
