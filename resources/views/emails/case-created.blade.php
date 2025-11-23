<x-mail::message>
# Novo Caso Registrado

Um novo caso de assistência a desastres foi criado no sistema.

## Detalhes do Caso

**Número do Caso:** {{ $caseNumber }}  
**Data de Atendimento:** {{ $serviceDate }}  
**Família:** {{ $family->responsiblePerson->full_name ?? 'N/A' }}  
**Ocorrência:** {{ $occurrence->description ?? 'N/A' }}

@if($family->address)
**Localização:** {{ $family->address->city }}, {{ $family->address->stateId->federation_unit ?? '' }}
@endif

O caso está disponível para revisão e processamento.

<x-mail::button :url="$actionUrl">
Ver Detalhes do Caso
</x-mail::button>

Esta é uma notificação automática do GESCAL.

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
