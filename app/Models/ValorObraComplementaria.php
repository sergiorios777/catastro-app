<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValorObraComplementaria extends Model
{
    protected $fillable = [
        'anio_fiscal_id',
        'catalogo_obra_complementaria_id',
        'zona_geografica',
        'valor'
    ];

    public function anioFiscal()
    {
        return $this->belongsTo(AnioFiscal::class);
    }

    // EL IMPORTADOR BUSCA ESTA FUNCIÓN "obra"
    public function obra()
    {
        // Nota: Especificamos la clave foránea porque el nombre de la función ('obra')
        // es diferente al de la columna ('catalogo_obra_complementaria_id')
        return $this->belongsTo(CatalogoObraComplementaria::class, 'catalogo_obra_complementaria_id');
    }
}
