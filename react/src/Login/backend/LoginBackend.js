
class LoginBackend {
    constructor() {
        this.f = null;
        this.l = null;
    }

    redirect(id, subcscription) {
        sessionStorage.setItem("id", id);
        sessionStorage.setItem("tier", subcscription);
        this.f();
    }

    redirectNewPass() {
        this.l();
    }
}

export default LoginBackend;