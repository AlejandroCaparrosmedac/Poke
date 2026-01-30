@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-3">
                <i class="fas fa-fire"></i> Crear Nueva Batalla
            </h1>
        </div>
    </div>

    <!-- Selector de Modo -->
    <div class="row mb-4">
        <div class="col-lg-8 offset-lg-2">
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="mode" id="mode-custom" value="custom" checked>
                <label class="btn btn-outline-primary" for="mode-custom">
                    <i class="fas fa-users"></i> Personalizado
                </label>

                <input type="radio" class="btn-check" name="mode" id="mode-random" value="random">
                <label class="btn btn-outline-success" for="mode-random">
                    <i class="fas fa-dice"></i> Aleatorio
                </label>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <form id="battle-form">
                        @csrf

                        <!-- Modo Personalizado -->
                        <div id="custom-mode" class="mode-section">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <h5 class="mb-3">Equipo del Jugador 1</h5>

                                    <div class="form-group">
                                        <label for="p1name" class="form-label">Nombre del Jugador</label>
                                        <input type="text" class="form-control" id="p1name" name="p1name"
                                               value="{{ auth()->user()->name }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="p1team" class="form-label">Equipo Pokémon</label>
                                        <textarea class="form-control" id="p1team" name="p1team" rows="5"
                                                  placeholder="Pikachu|Assault Vest|Lightning Rod|M|Thunderbolt,Quick Attack"></textarea>
                                        <small class="form-text text-muted">
                                            Formato: Nombre|Objeto|Habilidad|Género|Movimientos <a href="#" onclick="showTeamFormat()" class="text-decoration-none">Ver formato</a>
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <h5 class="mb-3">Equipo del Jugador 2 (IA)</h5>

                                    <div class="form-group">
                                        <label for="p2name" class="form-label">Nombre del Oponente</label>
                                        <input type="text" class="form-control" id="p2name" name="p2name"
                                               value="Rival IA" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="p2team" class="form-label">Equipo Pokémon</label>
                                        <textarea class="form-control" id="p2team" name="p2team" rows="5"
                                                  placeholder="Dragonite|Assault Vest|Multiscale|M|Outrage,Earthquake"></textarea>
                                        <small class="form-text text-muted">
                                            Equipo del oponente IA.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modo Aleatorio -->
                        <div id="random-mode" class="mode-section" style="display:none;">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <h5 class="mb-3">Configuración Jugador 1</h5>
                                    <div class="form-group">
                                        <label for="p1name-random" class="form-label">Tu Nombre</label>
                                        <input type="text" class="form-control" id="p1name-random" name="p1name"
                                               value="{{ auth()->user()->name }}" required>
                                    </div>
                                    <p class="text-muted">
                                        <i class="fas fa-dice"></i> El equipo será generado aleatoriamente
                                    </p>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <h5 class="mb-3">Configuración Jugador 2</h5>
                                    <div class="form-group">
                                        <label for="p2name-random" class="form-label">Nombre del Rival</label>
                                        <input type="text" class="form-control" id="p2name-random" name="p2name"
                                               value="Rival IA" required>
                                    </div>
                                    <p class="text-muted">
                                        <i class="fas fa-dice"></i> El equipo será generado aleatoriamente
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Generaciones (ambos modos) -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="formatId" class="form-label">Generación de Pokémon</label>
                                    <select class="form-control" id="formatId" name="formatId" required>
                                        <option value="gen9customgame">Gen 9 (Escarlata/Púrpura)</option>
                                        <option value="gen8customgame">Gen 8 (Espada/Escudo)</option>
                                        <option value="gen7customgame">Gen 7 (Sol/Luna)</option>
                                        <option value="gen6customgame">Gen 6 (X/Y)</option>
                                        <option value="gen5customgame">Gen 5 (Negro/Blanco)</option>
                                        <option value="gen4customgame">Gen 4 (Diamante/Perla)</option>
                                        <option value="gen3customgame">Gen 3 (Rojo/Azul)</option>
                                        <option value="gen2customgame">Gen 2 (Oro/Plata)</option>
                                        <option value="gen1customgame">Gen 1 (Rojo/Azul Original)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn">
                                    <i class="fas fa-play"></i> Iniciar Batalla
                                </button>
                                <a href="{{ route('battles.index') }}" class="btn btn-secondary btn-lg w-100 mt-2">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal de Formato -->
            <div class="modal fade" id="formatModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Formato de Equipo Pokémon Showdown</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>El formato es: <strong>Nombre|Objeto|Habilidad|Género|Movimientos|EVs|Naturaleza|IVs</strong></p>
                            <pre>Pikachu|Assault Vest|Lightning Rod|M|Thunderbolt,Quick Attack,Volt Switch,Thunder Wave|EVs: 4 Atk 252 SpA 252 Spe|Timid|</pre>
                            <p><strong>Partes:</strong></p>
                            <ul>
                                <li><strong>Nombre:</strong> Nombre del Pokémon</li>
                                <li><strong>Objeto:</strong> Objeto que sostiene (ej: Assault Vest)</li>
                                <li><strong>Habilidad:</strong> Habilidad del Pokémon</li>
                                <li><strong>Género:</strong> M (Macho) o F (Hembra)</li>
                                <li><strong>Movimientos:</strong> 4 movimientos separados por comas</li>
                                <li><strong>EVs:</strong> Puntos de esfuerzo (ej: EVs: 4 Atk 252 SpA 252 Spe)</li>
                                <li><strong>Naturaleza:</strong> Naturaleza del Pokémon (ej: Timid)</li>
                                <li><strong>IVs:</strong> Puntos individuales (opcional)</li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .mode-section {
        transition: all 0.3s ease;
    }

    .btn-group {
        margin-bottom: 2rem;
    }

    #custom-mode, #random-mode {
        padding: 1.5rem;
        border-radius: 0.5rem;
        background-color: var(--bs-body-bg);
    }
</style>

<script>
    // Alternar entre modo personalizado y aleatorio
    document.querySelectorAll('input[name="mode"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const customMode = document.getElementById('custom-mode');
            const randomMode = document.getElementById('random-mode');

            if (this.value === 'custom') {
                customMode.style.display = 'block';
                randomMode.style.display = 'none';
                document.getElementById('p1team').required = true;
                document.getElementById('p2team').required = true;
                document.getElementById('p1name').required = true;
                document.getElementById('p2name').required = true;
            } else {
                customMode.style.display = 'none';
                randomMode.style.display = 'block';
                document.getElementById('p1team').required = false;
                document.getElementById('p2team').required = false;
                // Los nombres siguen siendo requeridos
                document.getElementById('p1name-random').required = true;
                document.getElementById('p2name-random').required = true;
            }
        });
    });

    // Manejar envío del formulario
    document.getElementById('battle-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const mode = document.querySelector('input[name="mode"]:checked').value;
        const formData = new FormData();

        formData.append('_token', document.querySelector('[name="_token"]').value);
        formData.append('mode', mode);
        formData.append('formatId', document.getElementById('formatId').value);

        if (mode === 'custom') {
            // Validar que los equipos no estén vacíos
            const p1team = document.getElementById('p1team').value.trim();
            const p2team = document.getElementById('p2team').value.trim();

            if (!p1team || !p2team) {
                alert('Por favor, ingresa equipos válidos para ambos jugadores');
                return;
            }

            formData.append('p1name', document.getElementById('p1name').value);
            formData.append('p2name', document.getElementById('p2name').value);
            formData.append('p1team', p1team);
            formData.append('p2team', p2team);
        } else {
            // Modo aleatorio
            formData.append('p1name', document.getElementById('p1name-random').value);
            formData.append('p2name', document.getElementById('p2name-random').value);
        }

        // Mostrar indicador de carga
        const submitBtn = document.getElementById('submit-btn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando batalla...';

        // Enviar al servidor
        fetch('{{ route("battles.store") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw err;
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.battleId) {
                window.location.href = `/battles/${data.battleId}`;
            } else {
                alert('Error: ' + (data.message || 'No se pudo crear la batalla'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMsg = 'Ocurrió un error al crear la batalla';

            if (error.message) {
                errorMsg = error.message;
            }

            alert(errorMsg);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    // Mostrar modal de formato
    function showTeamFormat() {
        $('#formatModal').modal('show');
        return false;
    }
</script>
@endsection
