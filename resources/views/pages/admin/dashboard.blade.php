@php
use App\Models\Avaliacao;
use App\Models\Comentario;
use App\Models\Receita;
use App\Models\User;
$totais = ['Usuários' => User::count(), 'Receitas' => Receita::count(), 'Comentários' => Comentario::count(), 'Avaliações' => Avaliacao::count()];
$populares = Receita::with('categoria')->orderByDesc('visualizacoes_total')->take(5)->get();
@endphp

@extends('layouts.admin')
@section('heading', 'Visão geral')
@section('content')
<div class="admin-stat-grid">@foreach($totais as $label => $value)<div class="admin-stat"><span>{{ $label }}</span><strong>{{ number_format($value, 0, ',', '.') }}</strong><small>em toda a plataforma</small></div>@endforeach</div><div class="admin-content-grid"><section class="admin-panel"><div class="admin-panel-heading"><div><span class="eyebrow">Conteúdo em destaque</span><h2>Receitas mais acessadas</h2></div><a href="{{ route('receitas.listar') }}">Ver site →</a></div><div class="admin-table">@foreach($populares as $recipe)<div class="admin-table-row"><div><strong>{{ $recipe->titulo }}</strong><span>{{ $recipe->categoria->nome }}</span></div><b>{{ number_format($recipe->visualizacoes_total, 0, ',', '.') }} <small>views</small></b></div>@endforeach</div></section><section class="admin-panel admin-quick"><span class="eyebrow">Atalhos</span><h2>Gerencie seu conteúdo</h2><a href="{{ route('admin.categorias') }}">Organizar categorias <span>→</span></a><a href="{{ route('admin.comentarios') }}">Moderar comentários <span>→</span></a><a href="{{ route('admin.usuarios') }}">Ver usuários <span>→</span></a></section></div>
@endsection
