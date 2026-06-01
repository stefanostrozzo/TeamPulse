<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Ore Lavoro - {{ $selectedProject ? $selectedProject->name : 'Tutti i progetti' }}</title>
    <!-- Tailwind CSS per uno stile rapido e moderno -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                background-color: #ffffff;
                color: #000000;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-850 p-6 md:p-12 font-sans">
    
    <!-- Pulsante Stampa (Nascosto in stampa) -->
    <div class="max-w-4xl mx-auto mb-6 no-print flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-200">
        <span class="text-sm font-medium text-gray-600">Questo documento è progettato per essere stampato o salvato in PDF.</span>
        <button onclick="window.print()" class="bg-[#07b4f6] text-white font-semibold text-sm px-5 py-2.5 rounded-lg shadow hover:bg-[#069fd5] transition flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Stampa / Salva PDF
        </button>
    </div>

    <div class="max-w-4xl mx-auto bg-white p-8 md:p-12 rounded-2xl shadow-sm border border-gray-100 print:border-0 print:shadow-none">
        
        <!-- Intestazione -->
        <div class="flex justify-between items-start border-b border-gray-200 pb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">REPORT ATTIVITÀ</h1>
                <p class="text-gray-500 text-sm mt-1">Sintesi ore lavorate e compiti completati</p>
                
                <div class="mt-4 space-y-1 text-sm text-gray-600">
                    <div><strong class="text-gray-900">Progetto:</strong> {{ $selectedProject ? $selectedProject->name : 'Tutti i progetti' }}</div>
                    <div><strong class="text-gray-900">Periodo:</strong> 
                        @if($startDate && $endDate)
                            {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                        @else
                            {{ ucfirst($period) }} (Corrente)
                        @endif
                    </div>
                    @if(!$isTeam)
                        <div><strong class="text-gray-900">Consulente:</strong> {{ $user->name }} ({{ $user->email }})</div>
                    @else
                        <div><strong class="text-gray-900">Tipo Report:</strong> Report Generale del Team</div>
                    @endif
                </div>
            </div>
            
            <div class="text-right">
                <div class="inline-block bg-gray-100 rounded-2xl px-6 py-4 text-center">
                    <div class="text-xs text-gray-500 uppercase font-semibold tracking-wider">Ore Totali</div>
                    <div class="text-3xl font-bold text-gray-900 mt-1">
                        {{ floor($totalSeconds / 3600) }}h {{ floor(($totalSeconds % 3600) / 60) }}m
                    </div>
                </div>
                <div class="text-xs text-gray-400 mt-3">Documento generato il {{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <!-- Tabella riepilogativa se ci sono più progetti -->
        @if(!$selectedProject && count($byProject) > 1)
            <div class="mt-8">
                <h2 class="text-lg font-bold text-gray-900 mb-3">Ripartizione per Progetto</h2>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-150">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-gray-500 border-b border-gray-250 pb-2 font-semibold text-left">
                                <th class="py-1">Progetto</th>
                                <th class="text-right py-1">Tempo Dedicato</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($byProject as $pName => $seconds)
                                <tr>
                                    <td class="py-2 text-gray-700 font-medium">{{ $pName }}</td>
                                    <td class="py-2 text-right text-gray-900 font-semibold">{{ floor($seconds / 3600) }}h {{ floor(($seconds % 3600) / 60) }}m</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Registro Cronologico Giornaliero -->
        <div class="mt-10">
            <h2 class="text-xl font-bold text-gray-900 border-b pb-2 mb-6">Registro delle Attività</h2>

            @if($entriesGrouped->isEmpty())
                <p class="text-gray-500 text-center py-8">Nessun log temporale trovato per l'intervallo selezionato.</p>
            @else
                <div class="space-y-8">
                    @foreach($entriesGrouped as $date => $entries)
                        <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                            <!-- Intestazione Giorno -->
                            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200 flex justify-between items-center">
                                <strong class="text-gray-800 text-base">
                                    {{ \Carbon\Carbon::parse($date)->locale('it')->isoFormat('dddd D MMMM YYYY') }}
                                </strong>
                                <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full">
                                    {{ floor($entries->sum('duration_seconds') / 3600) }}h {{ floor(($entries->sum('duration_seconds') % 3600) / 60) }}m
                                </span>
                            </div>

                            <!-- Dettaglio delle attività del giorno -->
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-xs uppercase bg-gray-100/50 text-gray-500 border-b border-gray-250 text-left">
                                        <th class="px-5 py-2 font-semibold">Attività / Task</th>
                                        @if(!$selectedProject)
                                            <th class="px-5 py-2 font-semibold">Progetto</th>
                                        @endif
                                        @if($isTeam)
                                            <th class="px-5 py-2 font-semibold">Utente</th>
                                        @endif
                                        <th class="text-right px-5 py-2 font-semibold">Durata</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-150">
                                    @foreach($entries as $entry)
                                        <tr class="hover:bg-gray-50/50 transition duration-150">
                                            <td class="px-5 py-3">
                                                <div class="font-semibold text-gray-905">#{{ $entry->task_id }} {{ $entry->task->title }}</div>
                                                @if($entry->description)
                                                    <div class="text-xs text-gray-500 italic mt-1 bg-gray-50 p-2 rounded border border-gray-100">
                                                        {{ $entry->description }}
                                                    </div>
                                                @endif
                                            </td>
                                            @if(!$selectedProject)
                                                <td class="px-5 py-3 text-gray-600">{{ $entry->task->project->name ?? 'Senza Progetto' }}</td>
                                            @endif
                                            @if($isTeam)
                                                <td class="px-5 py-3 text-gray-600">{{ $entry->user->name }}</td>
                                            @endif
                                            <td class="px-5 py-3 text-right text-gray-900 font-semibold whitespace-nowrap">
                                                {{ floor($entry->duration_seconds / 3600) }}h {{ floor(($entry->duration_seconds % 3600) / 60) }}m
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Piè di pagina per fatturazione / firme -->
        <div class="mt-16 pt-12 border-t border-gray-200">
            <div class="grid grid-cols-2 gap-8 text-center text-sm text-gray-500">
                <div>
                    <p class="mb-12">Firma del Consulente</p>
                    <div class="border-b border-gray-400 mx-12"></div>
                </div>
                <div>
                    <p class="mb-12">Firma per Approvazione Cliente</p>
                    <div class="border-b border-gray-400 mx-12"></div>
                </div>
            </div>
        </div>

    </div>

    <!-- Auto attivazione stampa se aperta in una scheda vuota -->
    <script>
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('print') || true) {
                // auto apri il dialogo di stampa nativo del browser
                setTimeout(() => { window.print(); }, 600);
            }
        }
    </script>
</body>
</html>