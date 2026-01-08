<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Resources\Pages\ListRecords;

class ListClientes extends ListRecords
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('importar')
                ->label('Importar Clientes')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalDescription('El proceso puede tardar varios minutos dependiendo del tamaño del archivo. Por favor, no cierre la ventana.')
                ->modalCloseButton(false)
                ->closeModalByClickingAway(false)
                ->form([
                    \Filament\Forms\Components\FileUpload::make('archivo_excel')
                        ->label('Archivo Excel (.xlsx)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required(),
                    \Filament\Forms\Components\Placeholder::make('loading')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString('
                            <div 
                                x-data="{
                                    clicks: 0,
                                    showGame: false,
                                    scorePlayer1: 0,
                                    scorePlayer2: 0,
                                    mode: \'cpu\', // \'cpu\' or \'pvp\'
                                    keys: {},
                                    gameInterval: null,
                                    
                                    stopGame: function() {
                                        if (this.gameInterval) clearInterval(this.gameInterval);
                                        this.gameInterval = null;
                                        this.showGame = false;
                                        this.scorePlayer1 = 0;
                                        this.scorePlayer2 = 0;
                                        this.clicks = 0; 
                                    },

                                    toggleMode: function() {
                                        this.mode = (this.mode === \'cpu\') ? \'pvp\' : \'cpu\';
                                        this.scorePlayer1 = 0;
                                        this.scorePlayer2 = 0;
                                    },

                                    initGame: function() {
                                        const canvas = this.$refs.gameCanvas;
                                        if (!canvas) return;
                                        const ctx = canvas.getContext(\'2d\');
                                        let ball = { x: canvas.width/2, y: canvas.height/2, dx: 4, dy: 4, radius: 6 };
                                        let paddleHeight = 60, paddleWidth = 10;
                                        let player1Y = (canvas.height - paddleHeight) / 2;
                                        let player2Y = (canvas.height - paddleHeight) / 2;

                                        // Keyboard listeners
                                        window.addEventListener(\'keydown\', (e) => this.keys[e.key] = true);
                                        window.addEventListener(\'keyup\', (e) => this.keys[e.key] = false);

                                        // Mouse control for P1 (CPU mode)
                                        canvas.addEventListener(\'mousemove\', (e) => {
                                            if (this.mode === \'cpu\') {
                                                const rect = canvas.getBoundingClientRect();
                                                let root = document.documentElement;
                                                let mouseY = e.clientY - rect.top - root.scrollTop;
                                                player1Y = mouseY - (paddleHeight/2);
                                            }
                                        });

                                        if (this.gameInterval) clearInterval(this.gameInterval);

                                        this.gameInterval = setInterval(() => {
                                            if (!this.showGame) return;

                                            // MOVEMENT
                                            const speed = 5;
                                            
                                            // Player 1 (Left)
                                            if (this.mode === \'pvp\') {
                                                if (this.keys[\'w\'] || this.keys[\'W\']) player1Y -= speed;
                                                if (this.keys[\'s\'] || this.keys[\'S\']) player1Y += speed;
                                            }
                                            // Clamp P1
                                            if (player1Y < 0) player1Y = 0;
                                            if (player1Y > canvas.height - paddleHeight) player1Y = canvas.height - paddleHeight;


                                            // Player 2 (Right)
                                            if (this.mode === \'cpu\') {
                                                // AI
                                                let computerCenter = player2Y + (paddleHeight/2);
                                                if (computerCenter < ball.y - 35) {
                                                    player2Y += 4; 
                                                } else if (computerCenter > ball.y + 35) {
                                                    player2Y -= 4;
                                                }
                                            } else {
                                                // PVP
                                                if (this.keys[\'ArrowUp\']) player2Y -= speed;
                                                if (this.keys[\'ArrowDown\']) player2Y += speed;
                                            }
                                            // Clamp P2
                                            if (player2Y < 0) player2Y = 0;
                                            if (player2Y > canvas.height - paddleHeight) player2Y = canvas.height - paddleHeight;

                                            // BALL PHYSICS
                                            ball.x += ball.dx;
                                            ball.y += ball.dy;

                                            if (ball.y + ball.radius > canvas.height || ball.y - ball.radius < 0) {
                                                ball.dy = -ball.dy;
                                            }

                                            // Paddle collisions
                                            // Player 1
                                            if (ball.x - ball.radius < paddleWidth) {
                                                if (ball.y > player1Y && ball.y < player1Y + paddleHeight) {
                                                    ball.dx = -ball.dx;
                                                    let deltaY = ball.y - (player1Y + paddleHeight/2);
                                                    ball.dy = deltaY * 0.35;
                                                    if (Math.abs(ball.dx) < 12) ball.dx *= 1.05;
                                                } else if (ball.x < 0) {
                                                    this.scorePlayer2++;
                                                    this.resetBall(ball, canvas);
                                                }
                                            }
                                            // Player 2
                                            if (ball.x + ball.radius > canvas.width - paddleWidth) {
                                                if (ball.y > player2Y && ball.y < player2Y + paddleHeight) {
                                                    ball.dx = -ball.dx;
                                                    let deltaY = ball.y - (player2Y + paddleHeight/2);
                                                    ball.dy = deltaY * 0.35;
                                                    if (Math.abs(ball.dx) < 12) ball.dx *= 1.05;
                                                } else if (ball.x > canvas.width) {
                                                    this.scorePlayer1++;
                                                    this.resetBall(ball, canvas);
                                                }
                                            }

                                            // DRAW
                                            ctx.fillStyle = \'black\';
                                            ctx.fillRect(0, 0, canvas.width, canvas.height);
                                            
                                            // Net
                                            ctx.strokeStyle = \'rgba(255,255,255,0.2)\';
                                            ctx.beginPath();
                                            ctx.setLineDash([5, 15]);
                                            ctx.moveTo(canvas.width/2, 0);
                                            ctx.lineTo(canvas.width/2, canvas.height);
                                            ctx.stroke();

                                            // Ball
                                            ctx.fillStyle = \'white\';
                                            ctx.beginPath();
                                            ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI*2, false);
                                            ctx.fill();

                                            // Paddles
                                            ctx.fillStyle = \'white\';
                                            ctx.fillRect(0, player1Y, paddleWidth, paddleHeight);
                                            ctx.fillRect(canvas.width - paddleWidth, player2Y, paddleWidth, paddleHeight);

                                            // Scores
                                            ctx.font = \'30px Courier New\';
                                            ctx.textAlign = \'center\';
                                            ctx.fillText(this.scorePlayer1, canvas.width/4, 50);
                                            ctx.fillText(this.scorePlayer2, 3*canvas.width/4, 50);
                                            
                                        }, 1000/60);
                                    },
                                    resetBall: function(ball, canvas) {
                                        ball.x = canvas.width / 2;
                                        ball.y = canvas.height / 2;
                                        ball.dx = (Math.random() > 0.5 ? 4 : -4);
                                        ball.dy = (Math.random() * 6) - 3; 
                                    }
                                }"
                                class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors duration-300 min-h-[150px] flex items-center justify-center relative" 
                                wire:loading 
                                wire:target="callMountedAction"
                            >
                                <!-- Spinner View -->
                                <div x-show="!showGame" @click="clicks++; if(clicks >= 5) { showGame = true; $nextTick(() => initGame()); }" class="flex flex-col items-center justify-center gap-3 cursor-pointer select-none" title="Click me 5 times!">
                                    <svg class="animate-spin h-8 w-8 text-primary-600 dark:text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300 animate-pulse">
                                        Importando clientes, esto puede tardar un momento...
                                    </span>
                                    <span x-show="clicks > 2" class="text-xs text-gray-400 animate-bounce" x-text="7 - clicks + \' clicks remaining...\'"></span>
                                </div>

                                <!-- Game View -->
                                <div x-show="showGame" style="display: none;" class="flex flex-col items-center w-full">
                                    <div class="flex justify-between w-full max-w-[400px] mb-2 px-2">
                                        <button type="button" @click.prevent="toggleMode()" class="text-xs px-3 py-1 bg-gray-200 dark:bg-gray-800 rounded hover:bg-gray-300 dark:hover:bg-gray-700 transition font-bold" x-text="mode === \'cpu\' ? \'👥 2 Players\' : \'💻 vs CPU\'"></button>
                                        <button type="button" @click.prevent="stopGame()" class="text-xs px-3 py-1 bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300 rounded hover:bg-red-200 dark:hover:bg-red-800 transition font-bold">✕ Exit</button>
                                    </div>
                                    
                                    <canvas x-ref="gameCanvas" width="400" height="250" class="bg-black rounded shadow-lg border border-gray-600 cursor-none"></canvas>
                                    
                                    <p class="text-xs text-gray-500 mt-2 dark:text-gray-400 h-4 min-h-[1rem]">
                                        <span x-show="mode === \'cpu\'">Mouse: Move | First to 10 wins!</span>
                                        <span x-show="mode === \'pvp\'">P1: <b>W/S</b> | P2: <b>Arrows</b></span>
                                    </p>
                                </div>
                            </div>
                        ')),
                ])
                ->action(function (array $data) {
                    // set_time_limit(600); // Removed, using queue
                    $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($data['archivo_excel']);
                    $import = new \App\Imports\ClientsImport(auth()->user());
                    \Maatwebsite\Excel\Facades\Excel::queueImport($import, $filePath);

                    \Filament\Notifications\Notification::make()
                        ->title('Importación iniciada')
                        ->body("La importación de clientes ha comenzado en segundo plano. Recibirá una notificación cuando termine (si está configurado).")
                        ->success()
                        ->send();
                }),
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
