let Director = {

  results: [],
  changeScene:function (currentScene,result){

    this.results.push(result);
    game.scene.stop(currentScene);

    console.log(result);
    console.log(currentScene);
    switch (currentScene) {

      case "Intro": {

        game.scene.start("FocusGame");
        break;
      }
      case "FocusGame": {

        game.scene.start("ReadingGameTTSSentence");
        break;
      }


      case "ReadingGameTTSSentence": {
        if(result.result > 0.25) {
          game.scene.start("ReadingGameTTSWord");
        }else{
          game.scene.start("MemoryGameShortTerm");
        }
        break;
      }

      case "ReadingGameTTSWord": {

        if(result.result > 0.25){
          game.scene.start("ReadingGameText");
        }else{
          game.scene.start("MemoryGameShortTerm");
        }

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
