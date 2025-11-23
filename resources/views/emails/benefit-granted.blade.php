<x-mail::message>
# Benefício Concedido

Boas notícias! Um benefício foi concedido através do sistema GESCAL.

## Detalhes do Benefício

**Programa:** {{ $benefitProgram->name }}  
**Descrição:** {{ $benefitProgram->description ?? 'N/A' }}  
**Valor:** R$ {{ number_format($value, 2, ',', '.') }}  
**Data de Início:** {{ $startedAt }}

@if($beneficiary instanceof \App\Models\Person)
**Beneficiário:** {{ $beneficiary->full_name }}  
**CPF:** {{ $beneficiary->cpf_number ?? 'N/A' }}
@else
**Família Beneficiária:** {{ $beneficiary->responsiblePerson->full_name ?? 'N/A' }}
@endif

O benefício está ativo e registrado no sistema.

<x-mail::button :url="$actionUrl">
Ver Detalhes do Benefício
</x-mail::button>

Por favor, revise os detalhes do benefício e certifique-se de que toda a documentação está completa.

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
