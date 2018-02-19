package com.thm.mni.tals;


public class CasAuthenticationResult {
    private String token;
    private Exception exception;
    private String id;

    public CasAuthenticationResult() {
        this.token = null;
        this.id = null;
        this.exception = null;
    }

    public void setToken(String token) {
        this.token = token;
    }

    public void setUserId(String id) {
        this.id = id;
    }

    public void setException(Exception exception) {
        this.exception = exception;
    }

    public String getToken() {
        return token;
    }

    public String getUserId() {
        return id;
    }

    public Exception getException() {
        return exception;
    }
}
