<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\EquipoFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Equipo para el inventario de laboratorios de cómputo.
 *
 * [ENCAPSULAMIENTO] y [RELACION]
 * Claves foráneas acordadas con Miguel: laboratorio_id en equipos y equipo_id en mantenimientos.
 *
 * @property int $id
 * @property int $laboratorio_id
 * @property string $codigo_inventario
 * @property string $tipo
 * @property string $marca
 * @property string $modelo
 * @property string $numero_serie
 * @property Carbon $fecha_adquisicion
 * @property string $costo
 * @property string $estado
 * @property string|null $imagen
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Laboratorio $laboratorio
 * @property-read Collection<int, Mantenimiento> $mantenimientos
 */
class Equipo extends Model
{
    public const ESTADO_OPERATIVO = 'operativo';

    public const ESTADO_EN_REPARACION = 'en_reparacion';

    public const ESTADO_DE_BAJA = 'de_baja';

    /** @var list<string> */
    public const TIPOS = ['computadora', 'proyector', 'impresora', 'switch'];

    /** @var list<string> */
    public const ESTADOS = [self::ESTADO_OPERATIVO, self::ESTADO_EN_REPARACION, self::ESTADO_DE_BAJA];

    /** @use HasFactory<EquipoFactory> */
    use HasFactory;

    /**
     * Campos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'laboratorio_id',
        'codigo_inventario',
        'tipo',
        'marca',
        'modelo',
        'numero_serie',
        'fecha_adquisicion',
        'costo',
        'estado',
        'imagen',
    ];

    /**
     * [ENCAPSULAMIENTO] Las fechas y el dinero se manejan con sus tipos y precisión correctos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_adquisicion' => 'date',
            'costo' => 'decimal:2',
        ];
    }

    /**
     * [RELACION] Cada equipo pertenece a un laboratorio.
     */
    public function laboratorio(): BelongsTo
    {
        return $this->belongsTo(Laboratorio::class);
    }

    /**
     * [RELACION] Un equipo acumula muchos mantenimientos.
     */
    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class);
    }
}
