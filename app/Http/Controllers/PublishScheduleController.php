<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublishScheduleRequest;
use App\Http\Requests\UpdatePublishScheduleRequest;
use App\Models\PublishSchedule;
use App\Services\PublishScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublishScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(PublishScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index()
    {
        $user = Auth::user();
        $schedules = $this->scheduleService->getUserSchedules($user);
        $scheduleData = $this->scheduleService->formatScheduleDataForCalendar($schedules);

        return view('schedules.index', [
            'scheduleData' => $scheduleData,
        ]);
    }

    public function store(StorePublishScheduleRequest $request)
    {
        try {
            $schedule = $this->scheduleService->createSchedule(Auth::user(), $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Horario creado exitosamente',
                'schedule' => $schedule
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creando horario', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el horario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(PublishSchedule $schedule)
    {
        try {
            // Verificar que el schedule pertenece al usuario autenticado
            if ($schedule->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'schedule' => [
                    'id' => $schedule->id,
                    'day_of_week' => $schedule->day_of_week,
                    'time' => $schedule->time,
                    'is_active' => $schedule->is_active,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error obteniendo horario', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el horario'
            ], 500);
        }
    }

    public function update(UpdatePublishScheduleRequest $request, PublishSchedule $schedule)
    {
        try {
            // Verificar que el schedule pertenece al usuario autenticado
            if ($schedule->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado'
                ], 403);
            }

            $schedule->update([
                'day_of_week' => $request->day_of_week,
                'time' => $request->time,
                'is_active' => $request->has('is_active') ? (bool) $request->is_active : $schedule->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Horario actualizado exitosamente',
                'schedule' => $schedule
            ]);
        } catch (\Exception $e) {
            \Log::error('Error actualizando horario', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el horario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(PublishSchedule $schedule)
    {
        try {
            // Verificar que el schedule pertenece al usuario autenticado
            if ($schedule->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado'
                ], 403);
            }

            $schedule->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Horario eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error eliminando horario', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el horario: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggle(PublishSchedule $schedule)
    {
        try {
            // Verificar que el schedule pertenece al usuario autenticado
            if ($schedule->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado'
                ], 403);
            }

            $schedule->is_active = !$schedule->is_active;
            $schedule->save();

            return response()->json([
                'success' => true,
                'message' => $schedule->is_active ? 'Horario activado' : 'Horario pausado'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling horario', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del horario'
            ], 500);
        }
    }

    public function cloneDay(Request $request)
    {
        try {
            $this->scheduleService->cloneDaySchedules(
                Auth::user(),
                $request->input('from_day'),
                $request->input('to_day')
            );
            return response()->json([
                'success' => true,
                'message' => 'Horarios clonados exitosamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error clonando horarios', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al clonar horarios: ' . $e->getMessage()
            ], 500);
        }
    }
}