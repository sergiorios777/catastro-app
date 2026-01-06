# Documentación Técnica: Determinación Predial

Este documento detalla los procesos, lógica de negocio y artefactos involucrados en la funcionalidad de **Determinación Predial** (Cálculo del Impuesto Predial).

## 1. Descripción General

La **Determinación Predial** es el proceso mediante el cual se calcula el impuesto predial anual para un contribuyente (Persona) dentro de una jurisdicción (Tenant). Este proceso consolida todos los predios vigentes del contribuyente, calcula su autoavalúo individual, suma los totales y aplica la escala progresiva del impuesto predial (Alcabala/Tramos) y el Impuesto Mínimo.

## 2. Flujo del Proceso

El flujo principal es orquestado por el servicio `CalculoImpuestoService`.

1.  **Identificación de Predios**: Se obtienen todos los registros de `PropietarioPredio` vinculados a la persona en el año fiscal y tenant correspondientes, filtrando solo los vigentes (`vigente = true`).
2.  **Cálculo de Autoavalúo Individual**: Para cada predio, se invoca a `CalculoAutoavaluoService`.
    *   **Terreno**: Área * Arancel (Urbano o Rústico).
    *   **Construcciones**: Sumatoria de (Área Construida * Valor Unitario * (1 - Depreciación)).
    *   **Obras Complementarias**: Sumatoria de (Cantidad * Valor Unitario).
    *   **Ponderación**: Se aplica el porcentaje de propiedad (`porcentaje_propiedad`) al total del autoavalúo.
3.  **Sumatoria de Base Imponible**: Se suman los autoavalúos ponderados de todos los predios.
4.  **Cálculo del Impuesto**:
    *   Se aplica la escala progresiva (Tramos UIT) sobre la Base Imponible:
        *   **Tramo 1 (0-15 UIT)**: 0.2%
        *   **Tramo 2 (15-60 UIT)**: 0.6%
        *   **Tramo 3 (>60 UIT)**: 1.0%
    *   **Impuesto Mínimo**: Si el impuesto calculado es menor al mínimo legal (generalmente 0.6% de la UIT), se aplica el mínimo.
5.  **Generación de Snapshot**: Se crea una copia profunda (JSON) de la información del predio, construcciones y obras al momento del cálculo de la determinación.
6.  **Persistencia**: Se guarda o actualiza el registro en `DeterminacionPredial`.

## 3. Artefactos del Código

### A. Servicios (Lógica de Negocio)

*   **`App\Services\CalculoImpuestoService`**:
    *   **Rol**: Orquestador principal.
    *   **Funciones Clave**:
        *   `generarDeterminacion(array $sobrescrituras)`: Ejecuta todo el flujo.
        *   `calcularImpuestoEscalonado(float $baseImponible, float $uit)`: Aplica la lógica de tramos.
*   **`App\Services\CalculoAutoavaluoService`**:
    *   **Rol**: Calculadora detallada por predio físico.
    *   **Funciones Clave**:
        *   `calcularTotal()`: Devuelve terreno, construcción y obras.
        *   `obtenerValorUnitarioTotal()`: Calcula valor por m2 basado en categorías (Muros, Techos, etc.).
        *   `calcularTerreno()`, `calcularConstrucciones()`.

### B. Modelos (Eloquent)

*   **Principales**:
    *   `DeterminacionPredial`: Almacena el resultado de la determinación (`impuesto_calculado`, `base_imponible`, `snapshot_datos`).
    *   `PredioFisico`: Entidad principal del inmueble.
    *   `PredioFisicoAvaluo`: Historial de características físicas del predio usadas para el cálculo.
*   **Maestros / Configuración**:
    *   `AnioFiscal`: Contiene la UIT y Tasa Mínima del año.
    *   `ArancelUrbano` / `ArancelRustico`: Tablas de valores de terreno.
    *   `ValorUnitarioEdificacion`: Precios por m2 de componentes constructivos.
    *   `Depreciacion`: Factores de depreciación por antigüedad y estado.

### C. Recursos (Filament)

*   **`App\Filament\App\Resources\DeterminacionPredialResource`**:
    *   Interfaz administrativa para visualizar y gestionar las determinaciones.
    *   Usa `DeterminacionPredialForm` (Schema) y `DeterminacionPredialsTable` (Table).

## 4. Base de Datos

Tablas esenciales identificadas para este proceso:

| Tabla | Modelo Correspondiente | Propósito |
| :--- | :--- | :--- |
| `determinacion_predials` | `DeterminacionPredial` | Cabecera del cálculo del impuesto. |
| `predios_fisicos` | `PredioFisico` | Datos maestros del predio. |
| `predios_fisicos_avaluo` | `PredioFisicoAvaluo` | Versiones de datos valuatorios del predio. |
| `propietario_predio` | `PropietarioPredio` | Relación Persona-Predio (% propiedad). |
| `anios_fiscales` | `AnioFiscal` | Configuración anual (UIT). |
| `arancel_urbanos` | `ArancelUrbano` | Valores de terreno urbano. |
| `valor_unitario_edificacions` | `ValorUnitarioEdificacion` | Valores de construcción (CTAR). |

## 5. Características Esenciales

*   **Snapshotting (`snapshot_datos`)**:
    *   El campo `snapshot_datos` en `DeterminacionPredial` es crítico. Almacena un JSON con toda la estructura `PropietarioPredio -> PredioFisico -> [Construcciones, Obras]`.
    *   Esto permite "congelar" la información que dio origen al impuesto, sirviendo de *audit trail* histórico inmutable, independiente de cambios futuros en el predio.
*   **Simulación**:
    *   `generarDeterminacion` acepta un array de `$sobrescrituras`, permitiendo simular cálculos con valores hipotéticos sin persistir cambios en la BD.
