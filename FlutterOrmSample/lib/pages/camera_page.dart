import 'dart:io';

import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import './result_page.dart';
import 'package:google_mlkit_text_recognition/google_mlkit_text_recognition.dart';

class CameraPage extends StatefulWidget {
  const CameraPage({
    super.key,
    required this.camera,
  });

  final CameraDescription camera; // カメラの設定情報を受け取る

  @override
  State<CameraPage> createState() => _CameraPageState();
}

class _CameraPageState extends State<CameraPage> {
  late CameraController _controller; // カメラを制御するコントローラー
  late Future<void> _initializeControllerFuture; // カメラの初期化の完了を待つ

  final textRecognizer = TextRecognizer(script: TextRecognitionScript.japanese); // 日本語用のテキスト認識器

  @override
  void initState() {
    super.initState();

    // カメラの設定
    _controller = CameraController(
      widget.camera, // 受け取ったカメラ情報
      ResolutionPreset.max, // 最大解像度でカメラを設定
    );

    // コントローラーを初期化
    _initializeControllerFuture = _controller.initialize(); // カメラの初期化を非同期に行う
  }

  @override
  void dispose() {
    _controller.dispose(); // カメラコントローラーを破棄
    textRecognizer.close(); // テキスト認識器を閉じる
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // FutureBuilder でカメラの初期化が完了するのを待つ
    return Scaffold(
      appBar: AppBar(
        title: const Text('レシートを読み取って下さい'), // アプリバーのタイトル
      ),
      body: Padding(
        padding: const EdgeInsets.all(48.0), // 上下左右のパディング
        child: Center(
          child: FutureBuilder<void>(
            future: _initializeControllerFuture, // 初期化の完了を待つ
            builder: (context, snapshot) {
              if (snapshot.connectionState == ConnectionState.done) {
                // 初期化が完了したらカメラプレビューを表示
                return CameraPreview(_controller);
              } else {
                // 初期化が終わるまでインジケーターを表示
                return const Center(child: CircularProgressIndicator());
              }
            },
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () async {
          try {
            // 写真を撮るボタンが押された時
            final image = await _controller.takePicture(); // 画像を撮影

            final file = File(image.path); // 撮影した画像をファイルとして読み込む

            final inputImage = InputImage.fromFile(file); // ファイルをInputImageに変換

            // テキスト認識を実行
            final recognizedText = await textRecognizer.processImage(inputImage);
             // 認識結果を変数に格納
            String recognizedString = recognizedText.text;

            // テキストを表示するなどの処理を行う
            print("認識されたテキスト: $recognizedString");

            // 結果ページに遷移して認識結果を表示
            await Navigator.of(context).push(
              MaterialPageRoute(
                builder: (context) => ResultPage(recognizedText: recognizedText),
                fullscreenDialog: true,
              ),
            );

          } catch (e) {
            // 画像処理でエラーが発生した場合、エラーメッセージを表示
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('失敗'),
              ),
            );
          }
        },
        child: const Icon(Icons.camera_alt), // カメラのアイコン
      ),
    );
  }
}
