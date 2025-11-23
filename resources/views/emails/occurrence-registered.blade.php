<x-mail::message>
# Nova Ocorrência de Desastre Registrada

Uma nova ocorrência de desastre ou calamidade foi registrada no sistema GESCAL.

## Detalhes da Ocorrência

**Tipo:** {{ $occurrence->occurrenceType->name ?? 'N/A' }}  
**Data:** {{ $occurrence->occurrence_date ?? 'N/A' }}  
**Localização:** {{ $occurrence->location ?? 'N/A' }}

@if(isset($occurrence->description))
**Descrição:** {{ $occurrence->description }}
@endif

Atenção imediata pode ser necessária para famílias e indivíduos afetados.

<x-mail::button :url="$actionUrl">
Ver Detalhes da Ocorrência
</x-mail::button>

Esta é uma notificação automática do GESCAL.

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
