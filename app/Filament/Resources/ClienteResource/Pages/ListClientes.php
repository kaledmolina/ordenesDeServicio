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
                                    scorePlayer: 0,
                                    scoreComputer: 0,
                                    gameInterval: null,
                                    handleClick() {
                                        this.clicks++;
                                        if (this.clicks >= 7) {
                                            this.showGame = true;
                                            this.$nextTick(() => this.initGame());
                                        }
                                    },
                                    initGame() {
                                        const canvas = this.$refs.gameCanvas;
                                        if (!canvas) return;
                                        const ctx = canvas.getContext(\'2d\');
                                        let ball = { x: canvas.width/2, y: canvas.height/2, dx: 4, dy: 4, radius: 6 };
                                        let paddleHeight = 60, paddleWidth = 10;
                                        let playerY = (canvas.height - paddleHeight) / 2;
                                        let computerY = (canvas.height - paddleHeight) / 2;

                                        // Mouse control
                                        canvas.addEventListener(\'mousemove\', (e) => {
                                            const rect = canvas.getBoundingClientRect();
                                            let root = document.documentElement;
                                            let mouseY = e.clientY - rect.top - root.scrollTop;
                                            playerY = mouseY - (paddleHeight/2);
                                        });

                                        this.gameInterval = setInterval(() => {
                                            // Update
                                            ball.x += ball.dx;
                                            ball.y += ball.dy;

                                            // Wall collisions (top/bottom)
                                            if (ball.y + ball.radius > canvas.height || ball.y - ball.radius < 0) {
                                                ball.dy = -ball.dy;
                                            }

                                            // Paddle collisions
                                            // Player (Left)
                                            if (ball.x - ball.radius < paddleWidth) {
                                                if (ball.y > playerY && ball.y < playerY + paddleHeight) {
                                                    ball.dx = -ball.dx;
                                                    let deltaY = ball.y - (playerY + paddleHeight/2);
                                                    ball.dy = deltaY * 0.35;
                                                } else if (ball.x < 0) {
                                                    // Computer scores
                                                    this.scoreComputer++;
                                                    this.resetBall(ball, canvas);
                                                }
                                            }
                                            // Computer (Right)
                                            if (ball.x + ball.radius > canvas.width - paddleWidth) {
                                                if (ball.y > computerY && ball.y < computerY + paddleHeight) {
                                                    ball.dx = -ball.dx;
                                                    let deltaY = ball.y - (computerY + paddleHeight/2);
                                                    ball.dy = deltaY * 0.35;
                                                } else if (ball.x > canvas.width) {
                                                    // Player scores
                                                    this.scorePlayer++;
                                                    this.resetBall(ball, canvas);
                                                }
                                            }

                                            // AI Movement
                                            let computerCenter = computerY + (paddleHeight/2);
                                            if (computerCenter < ball.y - 35) {
                                                computerY += 6;
                                            } else if (computerCenter > ball.y + 35) {
                                                computerY -= 6;
                                            }

                                            // Draw
                                            ctx.fillStyle = \'black\';
                                            ctx.fillRect(0, 0, canvas.width, canvas.height);
                                            
                                            // Net
                                            ctx.strokeStyle = \'white\';
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
                                            ctx.fillRect(0, playerY, paddleWidth, paddleHeight);
                                            ctx.fillRect(canvas.width - paddleWidth, computerY, paddleWidth, paddleHeight);

                                            // Scores
                                            ctx.font = "20px Courier New";
                                            ctx.fillText(this.scorePlayer, 100, 50);
                                            ctx.fillText(this.scoreComputer, canvas.width - 100, 50);

                                        }, 1000/60);
                                    },
                                    resetBall(ball, canvas) {
                                        ball.x = canvas.width / 2;
                                        ball.y = canvas.height / 2;
                                        ball.dx = -ball.dx;
                                        ball.dy = 4;
                                    }
                                }"
                                class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 transition-colors duration-300 min-h-[150px] flex items-center justify-center" 
                                wire:loading 
                                wire:target="callMountedAction"
                            >
                                <!-- Spinner View -->
                                <div x-show="!showGame" @click="handleClick" class="flex flex-col items-center justify-center gap-3 cursor-pointer select-none" title="Click me 7 times!">
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
                                <div x-show="showGame" style="display: none;" class="flex flex-col items-center">
                                    <canvas x-ref="gameCanvas" width="400" height="250" class="bg-black rounded shadow-lg border border-gray-600 cursor-none"></canvas>
                                    <p class="text-xs text-gray-500 mt-2 dark:text-gray-400">Mueve el mouse para controlar la paleta izquierda. ¡Gana al CPU mientras esperas!</p>
                                </div>
                            </div>
                        ')),
                ])
                ->action(function (array $data) {
                    set_time_limit(600); // 10 minutes for large files
                    $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($data['archivo_excel']);
                    $import = new \App\Imports\ClientsImport;
                    \Maatwebsite\Excel\Facades\Excel::import($import, $filePath);

                    $created = $import->getCreatedCount();
                    $skipped = $import->getSkippedCount();

                    \Filament\Notifications\Notification::make()
                        ->title('Importación completada')
                        ->body("Se omitieron {$skipped} clientes por que ya estan registrados y se guardaron {$created} nuevos")
                        ->success()
                        ->send();
                }),
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
