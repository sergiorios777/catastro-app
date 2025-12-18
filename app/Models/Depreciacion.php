<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Depreciacion extends Model
{
    protected $fillable = [
        'material',
        'uso',
        'estado_conservacion',
        'antiguedad',
        'porcentaje',
    ];

    /**
     * Busca el % oficial según RNT.
     */
    public static function buscar($material, $uso, $estado, $antiguedad)
    {
        // Normalización RNT:
        // Si el material es Adobe o Madera, el RNT suele usar una tabla única 
        // sin importar si es tienda o casa (generalmente).
        // Si tu CSV trae datos específicos para Adobe+Tienda, lo respetamos.
        // Si no, forzamos el uso a 'otros' o 'casa_habitacion' según como cargues tu data.

        // Tope de antigüedad (El RNT suele topar en 50 años para concreto)
        /*if ($antiguedad > 50)
            $antiguedad = 50;*/

        $antiguedadClasificada = self::clasificarAntiguedad((int) $antiguedad);

        return self::where('material', $material)
            ->where('uso', $uso)
            ->where('estado_conservacion', $estado)
            ->where('antiguedad', $antiguedadClasificada)
            ->value('porcentaje');
        // Ojo: Si retorna null, significa que no hay dato oficial.
        // Ahí es donde entra la lógica manual que pediste.
    }

    private static function clasificarAntiguedad(int $antiguedad): int
    {
        $rangos = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50];

        foreach ($rangos as $valor) {
            if ($antiguedad <= $valor) {
                return $valor;
            }
        }

        return 99; // Mayor a 50
    }
}
