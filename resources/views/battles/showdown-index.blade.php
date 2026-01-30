@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-3">
                <i class="fas fa-fire"></i> Batallas Pokémon Showdown
            </h1>

            @if(!$battleServerAvailable)
                <div class="alert alert-danger" role="alert">
                    <strong>Servidor de batallas no disponible</strong>
                    <p>El servidor de Pokemon Showdown en puerto 9000 no está activo. Por favor, inicia el servidor:</p>
                    <code>cd pokemon-showdown-master && node battle-server.js</code>
                </div>
            @else
                <div class="alert alert-success" role="alert">
                    <strong>Servidor activo</strong>
                    <p>El servidor de batallas está disponible. ¡Puedes crear una batalla!</p>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Crear Nueva Batalla</h5>

                    @if($battleServerAvailable)
                        <a href="{{ route('battles.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus"></i> Iniciar Batalla
                        </a>
                    @else
                        <button class="btn btn-primary btn-lg" disabled>
                            <i class="fas fa-plus"></i> Iniciar Batalla (Servidor no disponible)
                        </button>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Batallas Activas</h5>
                    <div id="battles-list" style="min-height: 200px;">
                        <p class="text-muted">Cargando batallas...</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Mi Información</h5>
                    <dl class="row">
                        <dt class="col-sm-6">Usuario:</dt>
                        <dd class="col-sm-6">{{ auth()->user()->name }}</dd>

                        <dt class="col-sm-6">Pokémon favoritos:</dt>
                        <dd class="col-sm-6">
                            <span class="badge badge-primary">{{ count($userPokemon) }}</span>
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title">Requisitos</h5>
                    <ul class="small">
                        <li>Selecciona 2 Pokémon para tu equipo</li>
                        <li>Tu oponente tendrá IA automática</li>
                        <li>Máximo 10 turnos por batalla</li>
                        <li>Gana si debilitas a todos los Pokémon oponentes</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadActiveBattles();
    setInterval(loadActiveBattles, 5000); // Recargar cada 5 segundos
});

function loadActiveBattles() {
    fetch('{{ route("battles.list") }}')
        .then(response => response.json())
        .then(data => {
            const listContainer = document.getElementById('battles-list');

            if (!data.success || data.total === 0) {
                listContainer.innerHTML = '<p class="text-muted">No hay batallas activas en este momento</p>';
                return;
            }

            let html = '<div class="list-group">';
            data.battles.forEach(battle => {
                html += `
                    <a href="/battles/${battle.id}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${battle.p1name} vs ${battle.p2name}</h6>
                            <small class="text-muted">Turno ${battle.turn}</small>
                        </div>
                        <p class="mb-1 small"><strong>Formato:</strong> ${battle.formatId}</p>
                        <small class="text-muted">${new Date(battle.createdAt).toLocaleString()}</small>
                    </a>
                `;
            });
            html += '</div>';

            listContainer.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('battles-list').innerHTML = '<p class="text-danger">Error al cargar las batallas</p>';
        });
}
</script>
@endpush
@endsection
