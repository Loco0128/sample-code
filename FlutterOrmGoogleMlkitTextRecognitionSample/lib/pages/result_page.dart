import 'package:flutter/material.dart';
import 'package:google_mlkit_text_recognition/google_mlkit_text_recognition.dart';

class ResultPage extends StatelessWidget {
  final RecognizedText recognizedText;

  const ResultPage({super.key, required this.recognizedText});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('結果'),
      ),
      body: Container(
        padding: const EdgeInsets.all(30.0),
        child: Text(recognizedText.text),
      ),
    );
  }
}
