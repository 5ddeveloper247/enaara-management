<section class="mb-0">
    <header>
        <h2 class="h5 fw-semibold text-dark">
            {{ __('Delete Account') }}
        </h2>
        <p class="mt-1 text-muted small">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirm-user-deletion-modal">
        {{ __('Delete Account') }}
    </button>

    <div class="modal fade" id="confirm-user-deletion-modal" tabindex="-1" aria-labelledby="confirmUserDeletionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmUserDeletionLabel">
                            {{ __('Are you sure you want to delete your account?') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted small mb-0">
                            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                        </p>

                        <div class="mt-3">
                            <label for="password" class="form-label visually-hidden">{{ __('Password') }}</label>
                            <input id="password" name="password" type="password" class="form-control @if($errors->userDeletion->isNotEmpty()) is-invalid @endif" placeholder="{{ __('Password') }}">
                            @if($errors->userDeletion->has('password'))
                                <div class="form-text text-danger">{{ $errors->userDeletion->first('password') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-danger">
                            {{ __('Delete Account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@if($errors->userDeletion->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalEl = document.getElementById('confirm-user-deletion-modal');
    if (modalEl) {
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
});
</script>
@endif
