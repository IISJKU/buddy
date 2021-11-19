let Director = {

  changeScene:function (currentScene,result){

    game.scene.stop(currentScene);

    console.log(result);
    console.log(currentScene);
    switch (currentScene) {

      case "Intro": {

        game.scene.start("ReadingGameTTSWord");
        break;
      }
      case "FocusGame": {

        game.scene.start("ReadingGameTTSSentence");
        break;
      }

      case "ReadingGameTTSWord": {

        game.scene.start("ReadingGameTTSSentence");
        break;
      }
      case "ReadingGameTTSSentence": {

        game.scene.start("ReadingGameText");
        break;
      }
      case "ReadingGameText": {

        game.scene.start("WritingGame");
        break;
      }

      case "WritingGame": {

        game.scene.start("MathGame");
        break;
      }

      case "MathGame": {

        game.scene.start("MemoryGameShortTerm");
        break;
      }


      default: {

        break;
      }
    }


  }
}
