package com.arash.altafi.webSocket3

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.util.Log
import com.arash.altafi.webSocket3.databinding.ActivityMainBinding

class MainActivity : AppCompatActivity() {

    private val binding by lazy {
        ActivityMainBinding.inflate(layoutInflater)
    }
    private var webSocket: WebSocketClient? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(binding.root)

        init()
    }

    private fun init() = binding.apply {
        setupWebSocket()

        btnSend.setOnClickListener {
            webSocket?.send("test send")
        }
    }

    private fun setupWebSocket() = binding.apply {
        webSocket = WebSocketClient(URL) { message, isSuccess ->
            runOnUiThread {
                // Update textview with incoming message
                tvMessage.text = message
                chkStatus.isChecked = isSuccess
                Log.i("WebSocketClient", "isSuccess: $isSuccess")
                Log.i("WebSocketClient", "message: $message")
            }
        }
        webSocket?.connect()
    }

    override fun onDestroy() {
        super.onDestroy()
        webSocket?.disconnect()
    }

    private companion object {
        const val URL = "wss://socketsbay.com/wss/v2/1/demo/"
    }

}