package kr.devx.catcher;

import android.content.ContentValues;
import android.os.AsyncTask;
import android.text.TextUtils;
import android.util.Log;
import org.json.JSONException;
import org.json.JSONObject;

public class Catcher {

    private static final String TAG = "DEVX-CATCHER";
    private static final String URL_LOG_NEW = "https://api.devx.kr/Catcher/v1/log_new.php";

    private static String CATCHER_KEY = "";

    private static SAVE_TYPE DEFAULT_SAVE_TYPE = SAVE_TYPE.SAVE_PRINT;

    public interface LogSaveListener {
        void onLogSaveSuccess(int logIndex, CatcherLog catcherLog);
        void onLogSaveFail(int resultCode, String resultMessage);
        void onLogSaveError(String errorMessage);
    }

    public enum SAVE_TYPE {
        SAVE_SERVER, SAVE_PRINT, SAVE_BOTH
    }

    public static void init(String key) {
        CATCHER_KEY = key;
    }

    public static void init(String key, SAVE_TYPE defaultSaveType) {
        CATCHER_KEY = key;
        DEFAULT_SAVE_TYPE = defaultSaveType;
    }

    public static void saveLog(CatcherLog catcherLog) {
        saveLog(DEFAULT_SAVE_TYPE, catcherLog, null);
    }

    public static void saveLog(CatcherLog catcherLog, LogSaveListener listener) {
        saveLog(DEFAULT_SAVE_TYPE, catcherLog, null);
    }

    public static void saveLog(SAVE_TYPE saveType, CatcherLog catcherLog) {
        saveLog(DEFAULT_SAVE_TYPE, catcherLog, null);
    }

    public static void saveLog(SAVE_TYPE saveType, CatcherLog catcherLog, LogSaveListener listener) {
        if (saveType == null) saveType = DEFAULT_SAVE_TYPE;
        if (saveType == SAVE_TYPE.SAVE_PRINT || saveType == SAVE_TYPE.SAVE_BOTH) {
            StringBuilder packedLogString = new StringBuilder();
            packedLogString = packedLogString.append("[").append(catcherLog.getLevel()).append("]").append(catcherLog.getTitle()).append("\n").append("[").append(catcherLog.getTag()).append("][").append(catcherLog.getUser()).append("]");
            if (!TextUtils.isEmpty(packedLogString)) packedLogString = packedLogString.append("\n").append(catcherLog.getContent());
            Log.d(TAG, packedLogString.toString());
        }
        if (saveType == SAVE_TYPE.SAVE_SERVER || saveType == SAVE_TYPE.SAVE_BOTH) {
            NetworkTask networkTask = new NetworkTask(URL_LOG_NEW, catcherLog, listener);
            networkTask.execute();
        }
    }

    static class NetworkTask extends AsyncTask<Void, Void, String> {
        private final String url;
        private final CatcherLog log;
        private final ContentValues values;
        private final LogSaveListener listener;

        NetworkTask(String _url, CatcherLog _catcherLog, LogSaveListener _listener) {
            url = _url;
            listener = _listener;
            log = _catcherLog;
            values = new ContentValues();
            values.put("service_key", CATCHER_KEY);
            values.put("log_user", _catcherLog.getUser());
            values.put("log_tag", _catcherLog.getTag());
            values.put("log_level", _catcherLog.getLevel());
            values.put("log_title", _catcherLog.getTitle());
            values.put("log_content", _catcherLog.getContent());
        }

        @Override
        protected String doInBackground(Void... params) {

            String result;
            try {
                RequestHttpURLConnection requestHttpURLConnection = new RequestHttpURLConnection();
                result = requestHttpURLConnection.request(url, values);
            } catch (Exception e) {
                result = null;
                Log.w(TAG,"ERROR : " + e.getMessage());
                if (listener != null) listener.onLogSaveError(e.getMessage());
            }

            return result;
        }

        @Override
        protected void onPostExecute(String s) {
            super.onPostExecute(s);
            if (s == null) {
                return;
            }
            int resultCode = -1;
            int logIndex = -1;
            String errorMessage = "";
            try {
                JSONObject result = new JSONObject(s);
                resultCode = result.getInt("result");
                logIndex = result.optInt("log_index", -1);
                errorMessage = result.optString("error");
            } catch (JSONException e) {
                e.printStackTrace();
                Log.e(TAG,"ERROR : " + s);
                if (listener != null) listener.onLogSaveError(e.getMessage());
            }
            if (resultCode == 0) {
                if (listener != null) listener.onLogSaveSuccess(logIndex, log);
                Log.d(TAG,"SUCCESS : LOG ADDED : " + logIndex);
            } else {
                if (listener != null) listener.onLogSaveFail(resultCode, errorMessage);
                Log.e(TAG,"FAIL : " + errorMessage);
            }
        }
    }
}
