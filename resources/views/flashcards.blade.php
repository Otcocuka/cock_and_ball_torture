<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Флеш-карточки для интервью</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">
            📚 Флеш-карточки для подготовки
        </h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($questions as $question)
                <div 
                    class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 cursor-pointer group"
                    x-data="{ showFull: false }"
                    @click="showFull = !showFull"
                >
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">
                            {{ $question->question }}
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="text-gray-600 text-sm" :class="{ 'hidden': showFull }">
                                {{ Str::limit($question->answer_short, 100) }}
                            </div>
                            
                            <div 
                                class="text-green-600 text-sm"
                                :class="{ 'hidden': !showFull }"
                                x-show="showFull"
                                x-collapse
                            >
                                {{ $question->answer_full }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 transition-colors duration-300 group-hover:bg-gray-100">
                        <p class="text-xs text-gray-500 text-center">
                            <span x-text="showFull ? 'Нажми чтобы скрыть ответ' : 'Нажми чтобы показать ответ'"></span>
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>