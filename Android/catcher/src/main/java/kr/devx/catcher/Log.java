package kr.devx.catcher;

public class Log {
    private final String TAG;
    private final String USER;
    private final int LEVEL;
    private final String TITLE;
    private final String CONTENT;

    public static class Builder {
        private final String USER;
        private final int LEVEL;
        private String TAG = "";
        private String TITLE = "";
        private String CONTENT = "";

        public Builder(String _user, int _level) {
            this.USER = _user;
            this.LEVEL = _level;
        }

        public Builder tag(String _tag) {
            this.TAG = _tag;
            return this;
        }

        public Builder title(String _title) {
            this.TITLE = _title;
            return this;
        }

        public Builder content(String _content) {
            this.CONTENT = _content;
            return this;
        }

        public Log build() {
            return new Log(this);
        }
    }

    private Log(Builder builder) {
        TAG = builder.TAG;
        USER = builder.USER;
        LEVEL = builder.LEVEL;
        TITLE = builder.TITLE;
        CONTENT = builder.CONTENT;
    }

    public String getTag() {
        return TAG;
    }

    public String getUser() {
        return USER;
    }

    public int getLevel() {
        return LEVEL;
    }

    public String getTitle() {
        return TITLE;
    }

    public String getContent() {
        return CONTENT;
    }
}
