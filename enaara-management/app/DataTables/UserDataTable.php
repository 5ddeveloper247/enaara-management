<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<User> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('user_info', function (User $user) {
                return '<div class="d-flex align-items-center">' .
                    '<div class="user-avatar me-3">' . strtoupper(substr($user->name, 0, 2)) . '</div>' .
                    '<div><div class="fw-semibold">' . e($user->name) . '</div>' .
                    '<small class="text-muted">' . e($user->email) . '</small></div></div>';
            })
            ->addColumn('verified', function (User $user) {
                return $user->email_verified_at
                    ? '<span class="badge bg-success">Verified</span>'
                    : '<span class="badge bg-secondary">Unverified</span>';
            })
            ->addColumn('action', function (User $user) {
                return view('admin.users.columns.action', ['user' => $user])->render();
            })
            ->setRowId('id')
            ->rawColumns(['user_info', 'verified', 'action'])
            ->editColumn('created_at', fn (User $user) => $user->created_at?->format('M j, Y'))
            ->editColumn('updated_at', fn (User $user) => $user->updated_at?->format('M j, Y'))
            ->orderColumn('user_info', 'name $1')
            ->orderColumn('verified', 'email_verified_at $1');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<User>
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()->select(['id', 'name', 'email',]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('usersTable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1, 'asc')
            ->selectStyleSingle()
            ->parameters([
                'responsive' => true,
                'pageLength' => 25,
                'lengthMenu' => [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                'language' => [
                    'search' => '',
                    'searchPlaceholder' => 'Search users...',
                    'lengthMenu' => 'Show _MENU_ entries',
                    'info' => 'Showing _START_ to _END_ of _TOTAL_ entries',
                    'infoEmpty' => 'Showing 0 to 0 of 0 entries',
                    'infoFiltered' => '(filtered from _MAX_ total entries)',
                    'zeroRecords' => 'No matching records found',
                ],
                'dom' => '<"row px-4 py-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end"f>>rt<"row px-4 py-2"<"col-md-5"i><"col-md-7"p>>',
            ])
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('user_info')->title('User')->name('name')->orderable(true)->searchable(true),
            Column::make('verified')->title('Status')->name('email_verified_at')->orderable(true)->searchable(false),
            Column::computed('action')
                ->title('Actions')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-end'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
