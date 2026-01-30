@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-3">
                <i class="fas fa-fire"></i> Batalla en Progreso
            </h1>
        </div>
    </div>

    <div class="row">
        <!-- Área de Batalla -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Información de Jugadores -->
                    <div class="row mb-4">
                        <div class="col-md-6 text-center">
                            <h4>{{ $battle['p1name'] }}</h4>
                            <p class="text-muted">Turno: <strong>{{ $battle['turn'] }}</strong></p>
                        </div>
                        <div class="col-md-6 text-center">
                            <h4>{{ $battle['p2name'] }}</h4>
                            <p class="text-muted">vs</p>
                        </div>
                    </div>

                    <!-- Logs de Batalla -->
                    <div class="bg-dark text-white p-4 rounded" id="battle-logs" style="height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                        <p class="text-muted">Cargando logs de batalla...</p>
                    </div>

                    <!-- Controles -->
                    <div class="mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="p1move">Movimiento Jugador 1</label>
                                    <input type="text" class="form-control" id="p1move" placeholder="Ej: >move 1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="p2move">Movimiento Jugador 2 (IA)</label>
                                    <input type="text" class="form-control" id="p2move" placeholder="Auto" disabled>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary btn-lg btn-block" id="submit-turn" onclick="submitTurn()">
                            <i class="fas fa-arrow-right"></i> Enviar Turno
                        </button>

                        <button class="btn btn-danger btn-lg btn-block mt-2" onclick="finishBattle()">
                            <i class="fas fa-stop"></i> Finalizar Batalla
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Información de la Batalla</h5>
                    <dl class="row">
                        <dt class="col-sm-6">Batalla ID:</dt>
                        <dd class="col-sm-6 small"><code>{{ $battleId }}</code></dd>

                        <dt class="col-sm-6">Formato:</dt>
                        <dd class="col-sm-6">{{ $battle['formatId'] }}</dd>

                        <dt class="col-sm-6">Turno Actual:</dt>
                        <dd class="col-sm-6"><strong>{{ $battle['turn'] }}</strong></dd>

                        <dt class="col-sm-6">Tiempo Transcurrido:</dt>
                        <dd class="col-sm-6"><span id="elapsed-time">{{ $battle['elapsedSeconds'] }}s</span></dd>

                        <dt class="col-sm-6">Total de Logs:</dt>
                        <dd class="col-sm-6"><span id="total-logs">{{ $battle['totalLogs'] }}</span></dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title">Movimientos Disponibles</h5>
                    <p class="small text-muted">Formato de movimientos:</p>
                    <ul class="small">
                        <li><code>&gt;move 1</code> - Primer movimiento</li>
                        <li><code>&gt;move 2</code> - Segundo movimiento</li>
                        <li><code>&gt;move 3</code> - Tercer movimiento</li>
                        <li><code>&gt;move 4</code> - Cuarto movimiento</li>
                        <li><code>&gt;switch 2</code> - Cambiar a Pokémon 2</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title">Estado</h5>
                    <p id="battle-status" class="small">En progreso...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const battleId = '{{ $battleId }}';

document.addEventListener('DOMContentLoaded', function() {
    loadBattleLogs();
    setInterval(loadBattleLogs, 3000); // Recargar cada 3 segundos
});

function loadBattleLogs() {
    fetch(`/battles/${battleId}/logs`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const logsContainer = document.getElementById('battle-logs');
                logsContainer.innerHTML = data.logs.map(log => `<div>${escapeHtml(log)}</div>`).join('') || '<p class="text-muted">Sin logs aún</p>';
                logsContainer.scrollTop = logsContainer.scrollHeight;

                document.getElementById('total-logs').textContent = data.logs.length;
            }
        })
        .catch(error => console.error('Error:', error));
}

function submitTurn() {
    const p1move = document.getElementById('p1move').value.trim();
    const p2move = '>move ' + Math.floor(Math.random() * 4 + 1); // IA random

    if (!p1move) {
        alert('Por favor ingresa un movimiento válido');
        return;
    }

    const submitBtn = document.getElementById('submit-turn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

    fetch(`/battles/${battleId}/move`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            p1Move: p1move,
            p2Move: p2move
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('p1move').value = '';
            loadBattleLogs();
        } else {
            alert('Error: ' + (data.error || 'Error desconocido'));
        }

        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Enviar Turno';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al enviar el turno: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Enviar Turno';
    });
}

function finishBattle() {
    if (!confirm('¿Estás seguro de que quieres finalizar la batalla?')) {
        return;
    }

    fetch(`/battles/${battleId}/finish`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Batalla finalizada');
            window.location.href = '/battles';
        } else {
            alert('Error: ' + (data.error || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al finalizar la batalla');
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
@endsection
